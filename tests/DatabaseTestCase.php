<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        TestDatabase::connection();
    }

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::connection();
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->pdo->exec('TRUNCATE TABLE post_categories');
        $this->pdo->exec('TRUNCATE TABLE posts');
        $this->pdo->exec('TRUNCATE TABLE categories');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        http_response_code(200);
    }

    protected function insertCategory(int $id, string $name, string $desc = ''): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (id, name, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$id, $name, $desc]);
    }

    protected function insertPost(
        int    $id,
        string $title,
        string $publishedAt,
        int    $views = 0,
        ?int   $categoryId = null,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (id, title, description, content, image, views_count, published_at)
             VALUES (?, ?, ?, ?, NULL, ?, ?)'
        );
        $stmt->execute([$id, $title, "Desc: {$title}", "Content: {$title}", $views, $publishedAt]);

        if ($categoryId !== null) {
            $this->linkPostCategory($id, $categoryId);
        }
    }

    protected function linkPostCategory(int $postId, int $categoryId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)'
        );
        $stmt->execute([$postId, $categoryId]);
    }
}