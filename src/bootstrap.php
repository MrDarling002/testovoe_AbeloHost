<?php

declare(strict_types=1);

use App\Database\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config  = require dirname(__DIR__) . '/config/app.php';
$isDebug = $config['app_debug'];

try {
    new DateTimeZone($config['app_timezone'] ?? 'UTC');
} catch (\Throwable) {
    $config['app_timezone'] = 'UTC';
}

ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php_error.log');
error_reporting(E_ALL);

date_default_timezone_set('UTC');

set_exception_handler(function (Throwable $e) use ($isDebug): void {
    http_response_code(500);
    error_log(sprintf(
        "[%s] Uncaught: %s in %s:%d\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    if ($isDebug) {
        printf(
            '<h1>500</h1><pre>%s</pre>',
            htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
        );
    } else {
        echo '<h1>500</h1><p>Попробуйте позже.</p>';
    }
    exit;
});

$pdo = Connection::create($config['db']);

$smarty = new Smarty();
$smarty->setTemplateDir(dirname(__DIR__) . '/templates');
$smarty->setCompileDir(dirname(__DIR__) . '/storage/cache/compiled');
$smarty->setCacheDir(dirname(__DIR__) . '/storage/cache');
$smarty->escape_html = true;

$compileDir = dirname(__DIR__) . '/storage/cache/compiled';
if (!is_dir($compileDir)) {
    mkdir($compileDir, 0775, true);
}

return ['pdo' => $pdo, 'smarty' => $smarty, 'config' => $config];