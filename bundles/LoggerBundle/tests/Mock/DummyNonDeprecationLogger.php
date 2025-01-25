<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Tests\Mock;

use Otushomework\LoggerBundle\Logger\NonDeprecationLogger;

class DummyNonDeprecationLogger extends NonDeprecationLogger
{
    private array $logs = [];

    public function __construct()
    {
        parent::__construct('dummy.log');
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
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
