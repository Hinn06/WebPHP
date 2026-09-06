<?php
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require_once dirname(__DIR__) . '/src/Core/helpers.php';
require_once dirname(__DIR__) . '/src/Core/Auth.php';
require_once dirname(__DIR__) . '/src/Core/Database.php';
require_once dirname(__DIR__) . '/src/Core/Controller.php';
require_once dirname(__DIR__) . '/src/Core/Router.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require_once $file;
});

use App\Core\Database;
use App\Core\Router;

$pdo = Database::connection();
$router = new Router($pdo);
$router->dispatch($_GET['r'] ?? 'home');
