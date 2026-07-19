<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Core;

class RiskEngine
{
    private array $stats;
    private int $threshold;

    public function __construct(array $stats, int $threshold = 200)
    {
        $this->stats = $stats;
        $this->threshold = $threshold;
    }

    public function calculateScore(): int
    {
        $score = 0;

        if ($this->stats['avg'] > $this->threshold) {
            $score += 30;
        }

        if ($this->stats['error_rate'] > 5) {
            $score += 40;
        }

        if ($this->stats['max'] > $this->threshold * 10) {
            $score += 20;
        }

        if ($this->stats['max'] > $this->stats['avg'] * 5 && $this->stats['avg'] > 0) {
            $score += 10;
        }

        return min($score, 100);
    }

    public function getRecommendations(): array
    {
        $recommendations = [];

        if ($this->stats['avg'] > $this->threshold) {
            $recommendations[] = sprintf(
                '🔴 Rata-rata query lambat (%.2f ms). Pertimbangkan indexing atau optimasi query.',
                $this->stats['avg']
            );
        }

        if ($this->stats['error_rate'] > 5) {
            $recommendations[] = sprintf(
                '🔴 Error rate tinggi (%.2f%%). Periksa log error dan stabilitas koneksi.',
                $this->stats['error_rate']
            );
        }

        if ($this->stats['max'] > $this->threshold * 10) {
            $recommendations[] = sprintf(
                '🟡 Ada lonjakan waktu eksekusi (%.2f ms). Kemungkinan lock atau resource contention.',
                $this->stats['max']
            );
        }

        if ($this->stats['percentile_95'] > $this->threshold * 2) {
            $recommendations[] = sprintf(
                '🟡 95th percentile tinggi (%.2f ms). Periksa query pada beban puncak.',
                $this->stats['percentile_95']
            );
        }

        if ($this->stats['count'] > 1000 && $this->stats['avg'] < 50) {
            $recommendations[] = '✅ Performa sangat baik untuk jumlah iterasi besar. Pertahankan!';
        }

        return $recommendations;
    }
}