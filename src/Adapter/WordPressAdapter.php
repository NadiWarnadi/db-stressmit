<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Adapter;

class WordPressAdapter implements AdapterInterface
{
    private float $lastTime = 0.0;
    private array $queryLog = [];

    public function getConnection()
    {
        global $wpdb;
        return $wpdb;
    }

    public function query(string $sql): array
    {
        global $wpdb;

        $start = microtime(true);
        $result = $wpdb->get_results($sql, ARRAY_A);
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
        global $wpdb;
        return $wpdb->dbname;
    }
}