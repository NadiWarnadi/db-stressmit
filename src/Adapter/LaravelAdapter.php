<?php

namespace Warnadi\DbStressmit\Adapter;

class LaravelAdapter implements AdapterInterface
{
    private float $lastTime = 0.0;
    private array $queryLog = [];

    public function getConnection()
    {
        // Mengambil koneksi dari Laravel
        return \Illuminate\Support\Facades\DB::connection();
    }

    public function query(string $sql): array
    {
        $start = microtime(true);
        
        // Eksekusi pakai DB::select() bawaan Laravel
        $result = \Illuminate\Support\Facades\DB::select($sql);
        
        $this->lastTime = (microtime(true) - $start) * 1000; // ms
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
        return \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    }
}