<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$services = require dirname(__DIR__) . '/src/bootstrap.php';

(new App\Application($services['pdo'], $services['smarty'], $services['config']))
    ->run($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);