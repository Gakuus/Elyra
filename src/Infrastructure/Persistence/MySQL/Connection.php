<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

class Connection
{
    private static ?\PDO $instance = null;

    public static function get(): \PDO
    {
        if (self::$instance === null) {
            /** @var string $host */
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            /** @var string $port */
            $port = $_ENV['DB_PORT'] ?? '3306';
            /** @var string $database */
            $database = $_ENV['DB_DATABASE'] ?? 'elyra';
            /** @var string $username */
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            /** @var string $password */
            $password = $_ENV['DB_PASSWORD'] ?? '';
            /** @var string|null $unixSocket */
            $unixSocket = $_ENV['DB_SOCKET'] ?? null;

            if ($unixSocket) {
                $dsn = "mysql:unix_socket={$unixSocket};dbname={$database};charset=utf8mb4";
            } else {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            }

            self::$instance = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
