<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Logger;

class DebugLogger
{
    private bool $enabled;
    private string $log_path;

    public function __construct(bool $enabled, string $log_path)
    {
        $this->enabled = $enabled;
        $this->log_path = $log_path;
    }

    public function log(string $message): void
    {
        if (!$this->enabled) {
            return;
        }

        file_put_contents(
            $this->log_path,
            sprintf('[%s] DEBUG: %s%s', date('Y-m-d H:i:s'), $message, PHP_EOL),
            FILE_APPEND
        );
    }
}
