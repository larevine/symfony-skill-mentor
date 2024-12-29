<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Logger;

class NonDeprecationLogger
{
    private string $log_path;

    public function __construct(string $log_path)
    {
        $this->log_path = $log_path;
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $context_str = !empty($context) ? ' ' . json_encode($context) : '';
        file_put_contents(
            $this->log_path,
            sprintf('[%s] %s: %s%s%s', date('Y-m-d H:i:s'), strtoupper($level), $message, $context_str, PHP_EOL),
            FILE_APPEND
        );
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
}
