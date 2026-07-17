<?php

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

        // 1. Slow query (avg > threshold)
        if ($this->stats['avg'] > $this->threshold) {
            $score += 30;
        }

        // 2. Error rate > 5%
        if ($this->stats['error_rate'] > 5) {
            $score += 40;
        }

        // 3. Max > 10x threshold
        if ($this->stats['max'] > $this->threshold * 10) {
            $score += 20;
        }

        // 4. Variasi tinggi (max > 5x avg)
        if ($this->stats['max'] > $this->stats['avg'] * 5 && $this->stats['avg'] > 0) {
            $score += 10;
        }

        return min($score, 100);
    }

    public function getRecommendations(): array
    {
        $recommendations = [];

        if ($this->stats['avg'] > $this->threshold) {
            $recommendations[] = "🔴 Rata-rata query lambat ({$this->stats['avg']} ms). Pertimbangkan indexing atau optimasi query.";
        }

        if ($this->stats['error_rate'] > 5) {
            $recommendations[] = "🔴 Error rate tinggi ({$this->stats['error_rate']}%). Periksa log error dan stabilitas koneksi.";
        }

        if ($this->stats['max'] > $this->threshold * 10) {
            $recommendations[] = "🟡 Ada lonjakan waktu eksekusi ({$this->stats['max']} ms). Kemungkinan lock atau resource contention.";
        }

        if ($this->stats['percentile_95'] > $this->threshold * 2) {
            $recommendations[] = "🟡 95th percentile tinggi ({$this->stats['percentile_95']} ms). Periksa query pada beban puncak.";
        }

        if ($this->stats['count'] > 1000 && $this->stats['avg'] < 50) {
            $recommendations[] = "✅ Performa sangat baik untuk jumlah iterasi besar. Pertahankan!";
        }

        return $recommendations;
    }
}