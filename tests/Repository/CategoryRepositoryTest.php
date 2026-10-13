<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Repository\CategoryRepository;
use Tests\DatabaseTestCase;

class CategoryRepositoryTest extends DatabaseTestCase
{
    private CategoryRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new CategoryRepository($this->pdo);
    }

    public function testFindByIdReturnsCategory(): void
    {
        $this->insertCategory(1, 'Технологии', 'Описание');
        $cat = $this->repo->findById(1);

        $this->assertNotNull($cat);
        $this->assertSame(1, $cat['id']);
        $this->assertSame('Технологии', $cat['name']);
    }

    public function testFindByIdReturnsNullForMissing(): void
    {
        $this->assertNull($this->repo->findById(999));
    }
}