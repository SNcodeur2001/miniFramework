<?php

namespace App\Core\Abstract;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            // Lire les variables depuis .env
            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $dbname = defined('DB_NAME') ? DB_NAME : '';
            $dsn = defined('DSN') ? DSN : "$driver:host=$host;dbname=$dbname;charset=utf8mb4";
            $port = defined('DB_PORT') ? DB_PORT : 3306;
            $user = defined('DB_USER') ? DB_USER : '';
            $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';


            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        return self::$pdo;
    
    }
}
