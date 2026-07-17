<?php

namespace Warnadi\DbStressmit\Core;

class Profiler
{
    private array $durations = [];
    private int $errorCount = 0;
    private int $totalCount = 0;

    public function reset(): void
    {
        $this->durations = [];
        $this->errorCount = 0;
        $this->totalCount = 0;
    }

    public function record(float $duration, ?string $error = null): void
    {
        $this->totalCount++;
        if ($error !== null) {
            $this->errorCount++;
        } else {
            $this->durations[] = $duration;
        }
    }

    public function getStats(): array
    {
        $count = count($this->durations);
        if ($count === 0) {
            return [
                'count' => 0,
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'median' => 0,
                'percentile_95' => 0,
                'error_rate' => $this->totalCount > 0 ? round(($this->errorCount / $this->totalCount) * 100, 2) : 0,
            ];
        }

        sort($this->durations);
        $sum = array_sum($this->durations);
        $avg = $sum / $count;
        $median = $this->durations[intdiv($count, 2)];
        $p95Index = intval($count * 0.95) - 1;
        $p95 = $this->durations[$p95Index] ?? end($this->durations);

        return [
            'count' => $count,
            'min' => min($this->durations),
            'max' => max($this->durations),
            'avg' => $avg,
            'median' => $median,
            'percentile_95' => $p95,
            'error_rate' => $this->totalCount > 0 ? round(($this->errorCount / $this->totalCount) * 100, 2) : 0,
        ];
    }
}