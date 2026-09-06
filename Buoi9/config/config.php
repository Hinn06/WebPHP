<?php
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/BTL_web/public/index.php');
// Khong commit mat khau CSDL that vao repository.
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_NAME') ?: 'quan_ly_hoc_phan',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
];
