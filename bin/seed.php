<?php

declare(strict_types=1);

use App\Database\Connection;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = require dirname(__DIR__) . '/config/app.php';
$force  = in_array('--force', $argv, true);

try {
    $pdo = Connection::create($config['db']);
} catch (\PDOException $e) {
    fwrite(STDERR, "DB connection error: {$e->getMessage()}\n");
    exit(1);
}

$existingCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$existingPosts      = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();

if (($existingCategories > 0 || $existingPosts > 0) && !$force) {
    echo "Data already exists. Use --force to recreate.\n";
    exit(0);
}

$fixtures   = require dirname(__DIR__) . '/database/fixtures.php';
$categories = $fixtures['categories'];
$posts      = $fixtures['posts'];

echo "Seeding data...\n";

$pdo->beginTransaction();

try {
    if ($force) {
        $pdo->exec('DELETE FROM post_categories');
        $pdo->exec('DELETE FROM posts');
        $pdo->exec('DELETE FROM categories');
    }

    $catStmt = $pdo->prepare(
        'INSERT INTO categories (id, name, description) VALUES (:id, :name, :description)'
    );
    foreach ($categories as $i => $cat) {
        $catStmt->execute(['id' => $i + 1, 'name' => $cat['name'], 'description' => $cat['description']]);
    }
    echo "  Categories: " . count($categories) . "\n";

    $postStmt = $pdo->prepare(
        'INSERT INTO posts (id, title, description, content, image, views_count, published_at)
         VALUES (:id, :title, :description, :content, :image, :views_count, :published_at)'
    );

    $linkStmt = $pdo->prepare(
        'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)'
    );

    foreach ($posts as $i => $post) {
        $postId = $i + 1;
        $postStmt->execute([
            'id'           => $postId,
            'title'        => $post['title'],
            'description'  => $post['description'],
            'content'      => $post['content'],
            'image'        => $post['image'],
            'views_count'  => $post['views_count'],
            'published_at' => $post['published_at'],
        ]);

        foreach ($post['categories'] as $catId) {
            $linkStmt->execute(['post_id' => $postId, 'category_id' => $catId]);
        }
    }

    echo "  Posts: " . count($posts) . "\n";

    $pdo->commit();
    echo "Done.\n";

} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Seeding error: {$e->getMessage()}\n");
    exit(1);
}