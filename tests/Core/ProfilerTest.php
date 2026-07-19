<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Tests\Core;

use PHPUnit\Framework\TestCase;
use Warnadi\DbStressmit\Core\Profiler;

class ProfilerTest extends TestCase
{
    public function testRecordAndGetStats(): void
    {
        $profiler = new Profiler();
        
        $profiler->record(10.5);
        $profiler->record(20.2);
        $profiler->record(15.7);
        $profiler->record(12.3, 'Connection error');

        $stats = $profiler->getStats();

        $this->assertSame(3, $stats['count']);
        $this->assertSame(10.5, $stats['min']);
        $this->assertSame(20.2, $stats['max']);
        $this->assertEqualsWithDelta(15.47, $stats['avg'], 0.01);
        $this->assertSame(15.7, $stats['median']);
        $this->assertSame(20.2, $stats['percentile_95']);
        $this->assertEqualsWithDelta(25.0, $stats['error_rate'], 0.01);
    }

    public function testReset(): void
    {
        $profiler = new Profiler();
        $profiler->record(10.0);
        $profiler->reset();

        $stats = $profiler->getStats();
        $this->assertSame(0, $stats['count']);
        $this->assertSame(0.0, $stats['error_rate']);
    }

    public function testNoRecords(): void
    {
        $profiler = new Profiler();
        $stats = $profiler->getStats();

        $this->assertSame(0, $stats['count']);
        $this->assertSame(0.0, $stats['min']);
        $this->assertSame(0.0, $stats['max']);
        $this->assertSame(0.0, $stats['avg']);
        $this->assertSame(0.0, $stats['median']);
        $this->assertSame(0.0, $stats['percentile_95']);
        $this->assertSame(0.0, $stats['error_rate']);
    }

    public function testAllErrors(): void
    {
        $profiler = new Profiler();
        $profiler->record(0, 'Error 1');
        $profiler->record(0, 'Error 2');
        
        $stats = $profiler->getStats();
        
        $this->assertSame(0, $stats['count']);
        $this->assertSame(100.0, $stats['error_rate']);
    }
}