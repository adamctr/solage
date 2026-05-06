<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $envPath = dirname(__DIR__);
        if (file_exists($envPath . '/.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable($envPath);
            $dotenv->load();
        }
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
        foreach ($required as $key) {
            if (!isset($_ENV[$key]) && !isset($_SERVER[$key]) && getenv($key) === false) {
                throw new RuntimeException("Missing required env var: $key");
            }
        }

        $env      = static fn(string $k, ?string $default = null): ?string =>
            $_ENV[$k] ?? $_SERVER[$k] ?? (getenv($k) !== false ? getenv($k) : $default);

        $host     = $env('DB_HOST');
        $port     = $env('DB_PORT', '5432');
        $dbname   = $env('DB_NAME');
        $user     = $env('DB_USER');
        $password = $env('DB_PASSWORD');

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};options='--client_encoding=UTF8'";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->connection = new PDO($dsn, $user, $password, $options);
    }

    /**
     * @return PDO
     */
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
