<?php

declare(strict_types=1);

namespace Warnadi\DbStressmit\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Warnadi\DbStressmit\Adapter\CliAdapter;

class CliAdapterTest extends TestCase
{
    private CliAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set environment untuk SQLite in-memory
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['DB_USERNAME'] = '';
        $_ENV['DB_PASSWORD'] = '';
        $_ENV['DB_CHARSET'] = 'utf8';

        $this->adapter = new CliAdapter();
    }

    public function testGetConnection(): void
    {
        $connection = $this->adapter->getConnection();
        $this->assertInstanceOf(\PDO::class, $connection);
    }

    public function testQueryReturnsArray(): void
    {
        // Buat tabel dan insert data
        $this->adapter->query('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        $this->adapter->query("INSERT INTO test (name) VALUES ('John')");
        $this->adapter->query("INSERT INTO test (name) VALUES ('Jane')");

        $result = $this->adapter->query('SELECT * FROM test ORDER BY id');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('John', $result[0]['name']);
        $this->assertSame('Jane', $result[1]['name']);
    }

    public function testGetLastQueryTime(): void
    {
        $this->adapter->query('SELECT 1');
        $time = $this->adapter->getLastQueryTime();

        $this->assertIsFloat($time);
        $this->assertGreaterThan(0, $time);
    }

    public function testGetQueryLog(): void
    {
        $this->adapter->query('SELECT 1');
        $this->adapter->query('SELECT 2');

        $log = $this->adapter->getQueryLog();

        $this->assertIsArray($log);
        $this->assertCount(2, $log);
        $this->assertSame('SELECT 1', $log[0]['sql']);
        $this->assertSame('SELECT 2', $log[1]['sql']);
        $this->assertArrayHasKey('time', $log[0]);
        $this->assertIsFloat($log[0]['time']);
    }

    public function testGetDatabaseName(): void
    {
        $dbname = $this->adapter->getDatabaseName();
        $this->assertSame(':memory:', $dbname);
    }

    public function testQueryThrowsExceptionOnError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->adapter->query('SELECT * FROM nonexistent_table');
    }
}