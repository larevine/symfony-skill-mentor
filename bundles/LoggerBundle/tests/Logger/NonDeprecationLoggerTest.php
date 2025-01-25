<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Otushomework\LoggerBundle\Tests\Mock\DummyNonDeprecationLogger;

class NonDeprecationLoggerTest extends TestCase
{
    private DummyNonDeprecationLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new DummyNonDeprecationLogger();
    }

    public function testInfoLogIsStoredWithCorrectLevel(): void
    {
        $message = 'Test info message';
        $context = ['key' => 'value'];

        $this->logger->info($message, $context);

        $logs = $this->logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals([
            'level' => 'info',
            'message' => $message,
            'context' => $context
        ], $logs[0]);
    }

    public function testWarningLogIsStoredWithCorrectLevel(): void
    {
        $message = 'Test warning message';

        $this->logger->warning($message);

        $logs = $this->logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals([
            'level' => 'warning',
            'message' => $message,
            'context' => []
        ], $logs[0]);
    }

    public function testErrorLogIsStoredWithCorrectLevel(): void
    {
        $message = 'Test error message';

        $this->logger->error($message);

        $logs = $this->logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals([
            'level' => 'error',
            'message' => $message,
            'context' => []
        ], $logs[0]);
    }

    public function testClearRemovesAllLogs(): void
    {
        $this->logger->info('Info message');
        $this->logger->warning('Warning message');
        $this->logger->error('Error message');

        $this->logger->clear();

        $this->assertEmpty($this->logger->getLogs());
    }
}
