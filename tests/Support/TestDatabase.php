<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;
use RuntimeException;

class TestDatabase
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = env('TEST_DB_HOST', '127.0.0.1');
        $port = (int) env('TEST_DB_PORT', '3307');
        $name = env('TEST_DB_NAME', 'blog_test');
        $user = env('TEST_DB_USER', 'root');
        $pass = env('TEST_DB_PASSWORD', 'root_secret');

        if (!str_ends_with($name, '_test')) {
            throw new RuntimeException("Refusing to reset non-test database: {$name}");
        }

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
        ]);

        $pdo->exec("DROP DATABASE IF EXISTS `{$name}`");
        $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$name}`");

        $schema     = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
        $statements = explode(';', $schema);

        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '') {
                continue;
            }
            $lines    = explode("\n", $trimmed);
            $sqlLines = array_filter(
                $lines,
                static fn(string $line): bool
                    => !str_starts_with(ltrim($line), '--') && trim($line) !== ''
            );
            if (empty($sqlLines)) {
                continue;
            }
            $pdo->exec($statement);
        }

        self::$instance = $pdo;

        return $pdo;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}