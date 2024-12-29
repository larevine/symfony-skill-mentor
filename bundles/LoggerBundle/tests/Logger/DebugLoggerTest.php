<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Otushomework\LoggerBundle\Tests\Mock\DummyDebugLogger;

class DebugLoggerTest extends TestCase
{
    private DummyDebugLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new DummyDebugLogger();
    }

    public function testLogMessageIsStored(): void
    {
        $message = 'Test debug message';

        $this->logger->log($message);

        $logs = $this->logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals($message, $logs[0]);
    }

    public function testClearRemovesAllLogs(): void
    {
        $this->logger->log('First message');
        $this->logger->log('Second message');

        $this->logger->clear();

        $this->assertEmpty($this->logger->getLogs());
    }
}
