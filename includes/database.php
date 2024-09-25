<?php
//
// yueos_solage
// solagecyrineadamfabio

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = array(
            'host' => 'mysql-yueos.alwaysdata.net',
            'dbname'   => 'yueos_solage',
            'user' => 'yueos',
            'password' => 'solagecyrineadamfabio',
            'charset' => 'utf8mb4',
        );
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->connection = new PDO($dsn, $config['user'], $config['password'], $options);
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
