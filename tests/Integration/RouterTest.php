<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;
    private array  $called;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->called = [];

        $this->router->get('#^/$#', function () {
            $this->called[] = 'home';
        });

        $this->router->get('#^/category/(?<id>\d+)$#', function (array $params) {
            $this->called[] = 'category:' . $params['id'];
        });

        $this->router->get('#^/post/(?<id>\d+)$#', function (array $params) {
            $this->called[] = 'post:' . $params['id'];
        });
    }

    public function testHomeRoute(): void
    {
        $this->assertSame(Router::HANDLED, $this->router->dispatch('GET', '/'));
        $this->assertSame(['home'], $this->called);
    }

    public function testCategoryRoute(): void
    {
        $this->assertSame(Router::HANDLED, $this->router->dispatch('GET', '/category/42'));
        $this->assertSame(['category:42'], $this->called);
    }

    public function testCategoryRouteRejectsNonNumeric(): void
    {
        $this->assertSame(Router::NOT_FOUND, $this->router->dispatch('GET', '/category/abc'));
        $this->assertSame([], $this->called);
    }

    public function testPostRoute(): void
    {
        $this->assertSame(Router::HANDLED, $this->router->dispatch('GET', '/post/7'));
        $this->assertSame(['post:7'], $this->called);
    }

    public function testUnknownRouteReturnsNotFound(): void
    {
        $this->assertSame(Router::NOT_FOUND, $this->router->dispatch('GET', '/nonexistent'));
        $this->assertSame([], $this->called);
    }

    public function testHeadRequestHandled(): void
    {
        $this->assertSame(Router::HANDLED, $this->router->dispatch('HEAD', '/'));
        $this->assertSame(['home'], $this->called);
    }

    public function testTrailingSlashNormalised(): void
    {
        $this->assertSame(Router::HANDLED, $this->router->dispatch('GET', '/category/5/'));
        $this->assertSame(['category:5'], $this->called);
    }

    public function testPostMethodReturnsMethodNotAllowed(): void
    {
        $this->assertSame(Router::METHOD_NOT_ALLOWED, $this->router->dispatch('POST', '/post/1'));
        $this->assertSame([], $this->called);
    }
}