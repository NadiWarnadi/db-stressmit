<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Adapter;

use Illuminate\Support\Facades\DB;

class LaravelAdapter implements AdapterInterface
{
    private float $lastTime = 0.0;
    private array $queryLog = [];

    public function getConnection()
    {
        return DB::connection();
    }

    public function query(string $sql): array
    {
        $start = microtime(true);
        $result = DB::select($sql);
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
        return DB::connection()->getDatabaseName();
    }
}