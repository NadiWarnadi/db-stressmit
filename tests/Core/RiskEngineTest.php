<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Tests\Core;

use PHPUnit\Framework\TestCase;
use Warnadi\DbStressmit\Core\RiskEngine;

class RiskEngineTest extends TestCase
{
    public function testCalculateScoreWithSlowQuery(): void
    {
        $stats = [
            'count' => 10,
            'min' => 100,
            'max' => 500,
            'avg' => 250,
            'median' => 240,
            'percentile_95' => 450,
            'error_rate' => 0,
        ];

        $engine = new RiskEngine($stats, 200);
        $score = $engine->calculateScore();

        // avg > threshold (30) + max > 10x threshold? false (500 > 2000? no)
        $this->assertSame(30, $score);

        $recommendations = $engine->getRecommendations();
        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('rata-rata query lambat', $recommendations[0]);
    }

    public function testCalculateScoreWithHighErrorRate(): void
    {
        $stats = [
            'count' => 10,
            'min' => 10,
            'max' => 50,
            'avg' => 30,
            'median' => 28,
            'percentile_95' => 45,
            'error_rate' => 10,
        ];

        $engine = new RiskEngine($stats, 200);
        $score = $engine->calculateScore();

        // error_rate > 5 (40)
        $this->assertSame(40, $score);

        $recommendations = $engine->getRecommendations();
        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('Error rate tinggi', $recommendations[0]);
    }

    public function testCalculateScoreWithSpike(): void
    {
        $stats = [
            'count' => 10,
            'min' => 10,
            'max' => 2500,
            'avg' => 30,
            'median' => 28,
            'percentile_95' => 45,
            'error_rate' => 0,
        ];

        $engine = new RiskEngine($stats, 200);
        $score = $engine->calculateScore();

        // max > 10x threshold (2500 > 2000? yes -> 20)
        // max > 5x avg? 2500 > 150? yes -> 10
        // total 30
        $this->assertSame(30, $score);
    }

    public function testScoreCappedAt100(): void
    {
        $stats = [
            'count' => 10,
            'min' => 100,
            'max' => 5000,
            'avg' => 300,
            'median' => 280,
            'percentile_95' => 450,
            'error_rate' => 10,
        ];

        $engine = new RiskEngine($stats, 200);
        $score = $engine->calculateScore();

        // avg > threshold: 30
        // error_rate > 5: 40
        // max > 10x threshold: 20
        // max > 5x avg: 300 > 1500? no (karena avg 300, max 5000, 5000 > 1500? yes -> 10)
        // total 100
        $this->assertSame(100, $score);
    }

    public function testNoRecommendations(): void
    {
        $stats = [
            'count' => 10,
            'min' => 1,
            'max' => 5,
            'avg' => 2,
            'median' => 2,
            'percentile_95' => 4,
            'error_rate' => 0,
        ];

        $engine = new RiskEngine($stats, 200);
        $recommendations = $engine->getRecommendations();

        $this->assertEmpty($recommendations);
    }

    public function testGoodPerformanceRecommendation(): void
    {
        $stats = [
            'count' => 1500,
            'min' => 1,
            'max' => 30,
            'avg' => 10,
            'median' => 9,
            'percentile_95' => 25,
            'error_rate' => 0,
        ];

        $engine = new RiskEngine($stats, 200);
        $recommendations = $engine->getRecommendations();

        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('Performa sangat baik', $recommendations[0]);
    }
}