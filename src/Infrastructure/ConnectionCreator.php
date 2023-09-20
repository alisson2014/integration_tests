<?php

declare(strict_types=1);

namespace Alura\Auction\Infrastructure;

final class ConnectionCreator
{
    private static $pdo = null;

    public static function getConnection(): \PDO
    {
        if (is_null(self::$pdo)) {
            $path = __DIR__ . '/../../banco.sqlite';
            self::$pdo = new \PDO('sqlite:' . $path);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }
}
