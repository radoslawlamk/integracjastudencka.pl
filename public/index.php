<?php

declare(strict_types=1);

$sessionPath = __DIR__ . '/../storage/sessions';
if (is_dir($sessionPath)) {
    session_save_path($sessionPath);
}
session_start();

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['timezone']);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/../app/Core/Helpers.php';

use App\Core\Database;
use App\Core\Router;

Database::boot($config['db']);

$router = new Router();
require __DIR__ . '/../routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
