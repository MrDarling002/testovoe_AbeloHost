<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PostSort;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

class PostRepository
{
    private readonly DateTimeZone $utc;

    public function __construct(
        private readonly PDO          $db,
        private readonly DateTimeZone $displayTz,
    ) {
        $this->utc = new DateTimeZone('UTC');
    }

    public function findPublishedById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, description, content, image, views_count, published_at
             FROM posts
             WHERE id = :id AND published_at <= NOW()
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydratePost($row);
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE posts SET views_count = views_count + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    /** @return list<array{id: int, name: string}> */
    public function findCategoriesByPostId(int $postId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.name
             FROM categories c
             INNER JOIN post_categories pc ON c.id = pc.category_id
             WHERE pc.post_id = :post_id
             ORDER BY c.name'
        );
        $stmt->execute(['post_id' => $postId]);

        return array_map(
            static fn(array $r): array => ['id' => (int) $r['id'], 'name' => $r['name']],
            $stmt->fetchAll()
        );
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM posts p
             INNER JOIN post_categories pc ON p.id = pc.post_id
             WHERE pc.category_id = :cat_id AND p.published_at <= NOW()'
        );
        $stmt->execute(['cat_id' => $categoryId]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array> */
    public function findByCategory(int $categoryId, PostSort $sort, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT p.id, p.title, p.description, p.image, p.views_count, p.published_at
            FROM posts p
            INNER JOIN post_categories pc ON p.id = pc.post_id
            WHERE pc.category_id = :cat_id AND p.published_at <= NOW()
            ORDER BY {$sort->orderBy()}, p.id DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',    $perPage,     PDO::PARAM_INT);
        $stmt->bindValue(':off',    $offset,      PDO::PARAM_INT);
        $stmt->execute();

        return $this->hydrateMany($stmt->fetchAll());
    }

    /** @return list<array> */
    public function findSimilar(int $postId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.title, p.description, p.image,
                   p.views_count, p.published_at
            FROM posts p
            LEFT JOIN post_categories pc
                   ON pc.post_id = p.id
                  AND pc.category_id IN (
                        SELECT category_id
                        FROM post_categories
                        WHERE post_id = :source_id
                  )
            WHERE p.id <> :exclude_id
              AND p.published_at <= NOW()
            GROUP BY p.id
            ORDER BY COUNT(pc.category_id) DESC,
                     p.published_at DESC,
                     p.id DESC
            LIMIT :lim
        ");

        $stmt->bindValue(':source_id',  $postId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',        $limit,  PDO::PARAM_INT);
        $stmt->execute();

        return $this->hydrateMany($stmt->fetchAll());
    }

    /** @return list<array{category: array, posts: list<array>}> */
    public function findLatestGroupedByCategory(int $limit = 3): array
    {
        $sql = "
            SELECT
                c.id            AS category_id,
                c.name          AS category_name,
                c.description   AS category_description,
                ranked.id       AS post_id,
                ranked.title    AS post_title,
                ranked.description AS post_description,
                ranked.image    AS post_image,
                ranked.views_count,
                ranked.published_at
            FROM categories c
            INNER JOIN (
                SELECT
                    pc.category_id,
                    p.id,
                    p.title,
                    p.description,
                    p.image,
                    p.views_count,
                    p.published_at,
                    ROW_NUMBER() OVER (
                        PARTITION BY pc.category_id
                        ORDER BY p.published_at DESC, p.id DESC
                    ) AS rn
                FROM posts p
                INNER JOIN post_categories pc ON p.id = pc.post_id
                WHERE p.published_at <= NOW()
            ) ranked ON c.id = ranked.category_id AND ranked.rn <= :limit
            ORDER BY c.id, ranked.published_at DESC, ranked.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $catId = (int) $row['category_id'];
            if (!isset($result[$catId])) {
                $result[$catId] = [
                    'category' => [
                        'id'          => $catId,
                        'name'        => $row['category_name'],
                        'description' => $row['category_description'],
                    ],
                    'posts' => [],
                ];
            }
            $result[$catId]['posts'][] = $this->hydratePost([
                'id'           => $row['post_id'],
                'title'        => $row['post_title'],
                'description'  => $row['post_description'],
                'image'        => $row['post_image'],
                'views_count'  => $row['views_count'],
                'published_at' => $row['published_at'],
            ]);
        }

        return array_values($result);
    }

    private function hydratePost(array $row): array
    {
        $utc   = new DateTimeImmutable($row['published_at'], $this->utc);
        $local = $utc->setTimezone($this->displayTz);

        return [
            'id'                   => (int) $row['id'],
            'title'                => $row['title'],
            'description'          => $row['description'] ?? null,
            'content'              => $row['content'] ?? null,
            'image'                => $row['image'] ?: null,
            'views_count'          => (int) ($row['views_count'] ?? 0),
            'published_at'         => $row['published_at'],
            'published_at_iso'     => $utc->format(DATE_ATOM),
            'published_at_display' => $local->format('d.m.Y H:i'),
            'published_at_date'    => $local->format('d.m.Y'),
        ];
    }

    /** @return list<array> */
    private function hydrateMany(array $rows): array
    {
        return array_map(fn(array $r): array => $this->hydratePost($r), $rows);
    }
}