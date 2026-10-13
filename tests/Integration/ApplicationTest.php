<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application;
use Tests\DatabaseTestCase;

class ApplicationTest extends DatabaseTestCase
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $smarty = new \Smarty();
        $smarty->setTemplateDir(dirname(__DIR__, 2) . '/templates');
        $smarty->setCompileDir(sys_get_temp_dir() . '/smarty_app_test');
        $smarty->setCacheDir(sys_get_temp_dir() . '/smarty_app_test_cache');
        $smarty->escape_html = true;

        if (!is_dir(sys_get_temp_dir() . '/smarty_app_test')) {
            mkdir(sys_get_temp_dir() . '/smarty_app_test', 0775, true);
        }

        $config = [
            'app_timezone' => 'UTC',
            'pagination'   => ['per_page' => 10],
        ];

        $this->app = new Application($this->pdo, $smarty, $config);
    }

    public function testHomePageShowsCategoriesWithPosts(): void
    {
        $this->insertCategory(1, 'Технологии', 'Описание');
        $this->insertPost(1, 'Post', '2024-01-01 00:00:00', 0, 1);

        ob_start();
        $this->app->run('GET', '/');
        $output = ob_get_clean();

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('Технологии', $output);
    }

    public function testCategoryNotFoundReturns404(): void
    {
        ob_start();
        $this->app->run('GET', '/category/999');
        ob_end_clean();

        $this->assertSame(404, http_response_code());
    }

    public function testPostNotFoundReturns404(): void
    {
        ob_start();
        $this->app->run('GET', '/post/999');
        ob_end_clean();

        $this->assertSame(404, http_response_code());
    }

    public function testHeadDoesNotIncrementViews(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Post', '2024-01-01 00:00:00', 10, 1);

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        ob_start();
        $this->app->run('HEAD', '/post/1');
        ob_end_clean();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $stmt = $this->pdo->query('SELECT views_count FROM posts WHERE id = 1');
        $this->assertSame(10, (int) $stmt->fetchColumn());
    }

    public function testGetIncrementsViews(): void
    {
        $this->insertCategory(1, 'Cat');
        $this->insertPost(1, 'Post', '2024-01-01 00:00:00', 10, 1);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $this->app->run('GET', '/post/1');
        ob_end_clean();

        $stmt = $this->pdo->query('SELECT views_count FROM posts WHERE id = 1');
        $this->assertSame(11, (int) $stmt->fetchColumn());
    }

    public function testTrailingSlashRedirects301(): void
    {
        ob_start();
        $this->app->run('GET', '/category/1/');
        ob_end_clean();

        $this->assertSame(301, http_response_code());
    }

    public function testPostMethodReturns405(): void
    {
        ob_start();
        $this->app->run('POST', '/post/1');
        ob_end_clean();

        $this->assertSame(405, http_response_code());
    }
}