<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Model\PostSort;
use App\Repository\PostRepository;
use DateTimeZone;
use Tests\DatabaseTestCase;

class PostRepositoryTest extends DatabaseTestCase
{
    private PostRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PostRepository($this->pdo, new DateTimeZone('UTC'));
    }

    public function testFindPublishedByIdReturnsNullForMissing(): void
    {
        $this->assertNull($this->repo->findPublishedById(999));
    }

    public function testIncrementViewsIncreasesByOne(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Post', '2024-01-01 00:00:00', 10, 1);

        $this->repo->incrementViews(1);

        $post = $this->repo->findPublishedById(1);
        $this->assertSame(11, $post['views_count']);
    }

    public function testFindByCategorySortByDate(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Old', '2024-01-01 00:00:00', 100, 1);
        $this->insertPost(2, 'New', '2024-11-01 00:00:00', 5, 1);

        $posts = $this->repo->findByCategory(1, PostSort::Date, 1, 10);
        $this->assertSame(2, $posts[0]['id']);
        $this->assertSame(1, $posts[1]['id']);
    }

    public function testFindByCategorySortByViews(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Low', '2024-01-01 00:00:00', 5, 1);
        $this->insertPost(2, 'High', '2024-01-02 00:00:00', 500, 1);

        $posts = $this->repo->findByCategory(1, PostSort::Views, 1, 10);
        $this->assertSame(2, $posts[0]['id']);
    }

    public function testPaginationNoOverlap(): void
    {
        $this->insertCategory(1, 'Cat');
        for ($i = 1; $i <= 7; $i++) {
            $this->insertPost($i, "Post {$i}", "2024-01-0{$i} 00:00:00", 0, 1);
        }

        $all = [];
        for ($p = 1; $p <= 3; $p++) {
            $rows = $this->repo->findByCategory(1, PostSort::Date, $p, 3);
            foreach ($rows as $r) {
                $all[] = $r['id'];
            }
        }

        $this->assertCount(7, $all);
        $this->assertCount(7, array_unique($all));
    }

    public function testSimilarExcludesCurrentPost(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Current', '2024-01-03 00:00:00', 0, 1);
        $this->insertPost(2, 'Other 1', '2024-01-02 00:00:00', 0, 1);
        $this->insertPost(3, 'Other 2', '2024-01-01 00:00:00', 0, 1);

        $similar = $this->repo->findSimilar(1, 3);
        $ids     = array_column($similar, 'id');

        $this->assertNotContains(1, $ids);
        $this->assertCount(2, $ids);
    }

    public function testSimilarNoDuplicates(): void
    {
        $this->insertCategory(1, 'Cat A');
        $this->insertCategory(2, 'Cat B');
        $this->insertPost(1, 'Current', '2024-01-05 00:00:00', 0, 1);
        $this->linkPostCategory(1, 2);
        $this->insertPost(2, 'Shared', '2024-01-04 00:00:00', 0, 1);
        $this->linkPostCategory(2, 2);

        $similar = $this->repo->findSimilar(1, 3);
        $ids     = array_column($similar, 'id');

        $this->assertCount(count(array_unique($ids)), $ids);
    }

    public function testPostBelongsToManyCategories(): void
    {
        $this->insertCategory(1, 'Cat A');
        $this->insertCategory(2, 'Cat B');
        $this->insertPost(1, 'Multi', '2024-01-01 00:00:00', 0, 1);
        $this->linkPostCategory(1, 2);

        $cats = $this->repo->findCategoriesByPostId(1);
        $this->assertCount(2, $cats);
    }

    public function testCountByCategory(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'A', '2024-01-01 00:00:00', 0, 1);
        $this->insertPost(2, 'B', '2024-01-02 00:00:00', 0, 1);

        $this->assertSame(2, $this->repo->countByCategory(1));
    }

    public function testSimilarPrioritisesMoreSharedCategories(): void
    {
        $this->insertCategory(1, 'Cat A');
        $this->insertCategory(2, 'Cat B');

        $this->insertPost(1, 'Current', '2024-01-10 00:00:00', 0, 1);
        $this->linkPostCategory(1, 2);

        $this->insertPost(2, 'One shared', '2024-01-09 00:00:00', 0, 1);

        $this->insertPost(3, 'Two shared', '2024-01-08 00:00:00', 0, 1);
        $this->linkPostCategory(3, 2);

        $similar = $this->repo->findSimilar(1, 3);
        $ids     = array_column($similar, 'id');

        $this->assertSame(3, $ids[0]);
        $this->assertSame(2, $ids[1]);
    }

    public function testFutureDatedPostNotShown(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Published', '2024-01-01 00:00:00', 0, 1);
        $this->insertPost(2, 'Future', '2099-01-01 00:00:00', 0, 1);

        $this->assertNotNull($this->repo->findPublishedById(1));
        $this->assertNull($this->repo->findPublishedById(2));

        $posts = $this->repo->findByCategory(1, PostSort::Date, 1, 10);
        $ids   = array_column($posts, 'id');
        $this->assertNotContains(2, $ids);

        $grouped = $this->repo->findLatestGroupedByCategory(3);
        if (!empty($grouped)) {
            $postIds = array_column($grouped[0]['posts'], 'id');
            $this->assertNotContains(2, $postIds);
        }
    }

    public function testFindLatestGroupedByCategory(): void
    {
        $this->insertCategory(1, 'Cat A');
        $this->insertCategory(2, 'Cat B');
        for ($i = 1; $i <= 5; $i++) {
            $this->insertPost($i, "Post {$i}", "2024-11-0{$i} 10:00:00", 0, 1);
        }
        $this->insertPost(6, 'Cat B post', '2024-11-01 10:00:00', 0, 2);

        $result = $this->repo->findLatestGroupedByCategory(3);

        $this->assertCount(2, $result);
        $this->assertCount(3, $result[0]['posts']);
        $this->assertCount(1, $result[1]['posts']);

        $ids = array_column($result[0]['posts'], 'id');
        $this->assertSame([5, 4, 3], $ids);
    }
}