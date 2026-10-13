<?php

declare(strict_types=1);

namespace App;

class Router
{
    public const HANDLED            = 'handled';
    public const NOT_FOUND          = 'not_found';
    public const METHOD_NOT_ALLOWED = 'method_not_allowed';

    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method'  => 'GET',
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): string
    {
        $isHead          = ($method === 'HEAD');
        $effectiveMethod = $isHead ? 'GET' : $method;

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $pathMatched = true;

            if ($route['method'] !== $effectiveMethod) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            if ($isHead) {
                ob_start();
                try {
                    ($route['handler'])($params);
                } finally {
                    ob_end_clean();
                }
            } else {
                ($route['handler'])($params);
            }

            return self::HANDLED;
        }

        return $pathMatched ? self::METHOD_NOT_ALLOWED : self::NOT_FOUND;
    }
}