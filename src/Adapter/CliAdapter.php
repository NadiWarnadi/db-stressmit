<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Adapter;

use Symfony\Component\Dotenv\Dotenv;
use PDO;
use PDOException;

class CliAdapter implements AdapterInterface
{
    private PDO $pdo;
    private float $lastTime = 0.0;
    private array $queryLog = [];

    public function __construct()
    {
        $envPath = $this->findEnvPath();
        if ($envPath !== null && file_exists($envPath)) {
            $dotenv = new Dotenv();
            $dotenv->load($envPath);
        }

        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'test';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $driver,
            $host,
            $port,
            $dbname,
            $charset
        );

        try {
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    private function findEnvPath(): ?string
    {
        $candidates = [
            __DIR__ . '/../../../.env',
            __DIR__ . '/../../.env',
            getcwd() . '/.env',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql): array
    {
        $start = microtime(true);
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->lastTime = (microtime(true) - $start) * 1000;
        $this->queryLog[] = ['sql' => $sql, 'time' => $this->lastTime];
        return $result;
    }

    public function getLastQueryTime(): float
    {
        return $this->lastTime;
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function getDatabaseName(): string
    {
        return $this->pdo->query('SELECT DATABASE()')->fetchColumn();
    }
}