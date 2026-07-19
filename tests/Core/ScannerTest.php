<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Warnadi\DbStressmit\Core\Scanner;
use Warnadi\DbStressmit\Adapter\AdapterInterface;

class ScannerTest extends TestCase
{
    private AdapterInterface&MockObject $adapter;
    private Scanner $scanner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->scanner = new Scanner($this->adapter);
    }

    public function testRunReturnsStats(): void
    {
        $this->adapter
            ->expects($this->exactly(5))
            ->method('query')
            ->with('SELECT 1')
            ->willReturn([['1' => 1]]);

        $stats = $this->scanner->run('SELECT 1', 5);

        $this->assertSame(5, $stats['count']);
        $this->assertGreaterThan(0, $stats['min']);
        $this->assertGreaterThan(0, $stats['avg']);
    }

    public function testRunWithConcurrency(): void
    {
        $this->adapter
            ->expects($this->exactly(10))
            ->method('query')
            ->with('SELECT 1')
            ->willReturn([['1' => 1]]);

        $stats = $this->scanner->run('SELECT 1', 5, 2);

        $this->assertSame(10, $stats['count']);
    }

    public function testRunWithErrors(): void
    {
        $this->adapter
            ->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function ($sql) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 2) {
                    throw new \Exception('Simulated database error');
                }
                return [['1' => 1]];
            });

        $stats = $this->scanner->run('SELECT 1', 3);

        $this->assertSame(2, $stats['count']);
        $this->assertEqualsWithDelta(33.33, $stats['error_rate'], 0.01);
    }

    public function testGetProfiler(): void
    {
        $profiler = $this->scanner->getProfiler();
        $this->assertInstanceOf(\Warnadi\DbStressmit\Core\Profiler::class, $profiler);
    }
}