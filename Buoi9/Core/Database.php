<?php
namespace App\Core;
class Database {
    private static ?\PDO $pdo = null;
    public static function connection(): \PDO {
        if (self::$pdo) return self::$pdo;
        $cfg = require dirname(__DIR__, 2) . '/config/config.php'; $d=$cfg['db'];
        $dsn="mysql:host={$d['host']};dbname={$d['database']};charset={$d['charset']}";
        self::$pdo=new \PDO($dsn,$d['username'],$d['password'],[
            \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES=>false,
        ]); return self::$pdo;
    }
}
