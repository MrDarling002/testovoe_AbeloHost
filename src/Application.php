<?php

declare(strict_types=1);

namespace App;

use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Controller\PostController;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeZone;
use PDO;
use Smarty;

class Application
{
    public function __construct(
        private readonly PDO    $pdo,
        private readonly Smarty $smarty,
        private readonly array  $config,
    ) {
    }

    public function run(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $query    = parse_url($uri, PHP_URL_QUERY);
            $redirect = rtrim($path, '/');
            if ($redirect === '') {
                $redirect = '/';
            }
            if ($query !== null && $query !== '') {
                $redirect .= '?' . $query;
            }
            header('Location: ' . $redirect, true, 301);
            return;
        }

        header('Content-Type: text/html; charset=utf-8');

        $displayTz    = new DateTimeZone($this->config['app_timezone'] ?? 'UTC');
        $categoryRepo = new CategoryRepository($this->pdo);
        $postRepo     = new PostRepository($this->pdo, $displayTz);
        $perPage      = (int) ($this->config['pagination']['per_page'] ?? 10);

        $router = new Router();

        $router->get('#^/$#', function () use ($postRepo): void {
            (new HomeController($this->smarty, $postRepo))->index();
        });

        $router->get('#^/category/(?<id>\d+)$#', function (array $p) use ($categoryRepo, $postRepo, $perPage): void {
            (new CategoryController($this->smarty, $categoryRepo, $postRepo, $perPage))->show((int) $p['id']);
        });

        $router->get('#^/post/(?<id>\d+)$#', function (array $p) use ($postRepo): void {
            (new PostController($this->smarty, $postRepo))->show((int) $p['id']);
        });

        $result = $router->dispatch($method, $uri);
        $isHead = ($method === 'HEAD');

        if ($result === Router::NOT_FOUND) {
            http_response_code(404);
            if (!$isHead) {
                $this->smarty->assign('pageTitle', 'Страница не найдена');
                $this->smarty->display('not_found.tpl');
            }
        } elseif ($result === Router::METHOD_NOT_ALLOWED) {
            http_response_code(405);
            header('Allow: GET, HEAD');
            if (!$isHead) {
                echo '<h1>405</h1><p>Метод не поддерживается.</p>';
            }
        }
    }
}