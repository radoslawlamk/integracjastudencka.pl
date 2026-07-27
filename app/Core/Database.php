<?php

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function boot(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $driver = self::$config['driver'];
        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . self::$config['database'];
            self::$pdo = new PDO($dsn);
        } else {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['database'],
                self::$config['charset']
            );
            self::$pdo = new PDO($dsn, self::$config['username'], self::$config['password']);
        }

        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return self::$pdo;
    }
}
