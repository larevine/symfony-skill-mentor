<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Tests\Mock;

use Otushomework\LoggerBundle\Logger\DebugLogger;

class DummyDebugLogger extends DebugLogger
{
    private array $logs = [];

    public function __construct()
    {
        parent::__construct(true, 'dummy.log');
    }

    public function log(string $message): void
    {
        $this->logs[] = $message;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function clear(): void
    {
        $this->logs = [];
    }
}
