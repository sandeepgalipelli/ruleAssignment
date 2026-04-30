<?php

class Database
{
    private static ?\PDO $pdo = null;

    private static array $config = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'rule_assignment_db',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ];

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $cfg = self::$config;

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['name'],
                $cfg['charset']
            );

            self::$pdo = new PDO(
                $dsn,
                $cfg['user'],
                $cfg['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}