<?php // app/modeles/Database.php
class Database 
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $user = DB_USER;
        $pass = DB_PASS;
        $this->connection = new PDO($dsn, $user, $pass);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function __clone() {
    }

    public function __wakeup() {
    }
}