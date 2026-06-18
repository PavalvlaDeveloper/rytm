<?php
declare(strict_types=1);

namespace RyTM\Core;

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            require_once __DIR__ . '/../../config/db.php';
            self::$connection = mysqli_connect(
                DB_HOST,
                DB_USERNAME,
                DB_PASSWORD,
                DB_NAME,
                (int)DB_PORT
            );
            if (self::$connection === false) {
                die('Ошибка подключения к БД: ' . mysqli_connect_error());
            }
            mysqli_set_charset(self::$connection, 'utf8mb4');
        }
        return self::$connection;
    }
}