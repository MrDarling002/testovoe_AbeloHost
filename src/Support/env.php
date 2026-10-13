<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return (string) $value;
    }
}