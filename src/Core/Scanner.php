<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Core;

use Warnadi\DbStressmit\Adapter\AdapterInterface;

class Scanner
{
    private AdapterInterface $adapter;
    private Profiler $profiler;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->profiler = new Profiler();
    }

    public function run(string $sql, int $iterations, int $concurrency = 1): array
    {
        $this->profiler->reset();

        for ($i = 0; $i < $concurrency; $i++) {
            for ($j = 0; $j < $iterations; $j++) {
                $this->executeQuery($sql);
            }
        }

        return $this->profiler->getStats();
    }

    private function executeQuery(string $sql): void
    {
        $start = microtime(true);
        $error = null;

        try {
            $this->adapter->query($sql);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $duration = (microtime(true) - $start) * 1000;
        $this->profiler->record($duration, $error);
    }

    public function getProfiler(): Profiler
    {
        return $this->profiler;
    }
}