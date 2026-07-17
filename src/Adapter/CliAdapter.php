<?php

namespace Warnadi\DbStressmit\Adapter;

class CliAdapter implements AdapterInterface
{
    private \PDO $pdo;
    private float $lastTime = 0.0;
    private array $queryLog = [];

    public function __construct()
    {
        // Bisa baca dari .env atau param CLI, untuk sementara kita hardcode contoh
        // Nanti kita upgrade pakai Symfony Console input
        $dsn = $_ENV['DB_DSN'] ?? 'mysql:host=localhost;dbname=test';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $this->pdo = new \PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function query(string $sql): array
    {
        $start = microtime(true);
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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