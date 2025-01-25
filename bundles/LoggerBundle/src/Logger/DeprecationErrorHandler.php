<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Logger;

class DeprecationErrorHandler
{
    private bool $enabled;
    private string $log_path;

    public function __construct(bool $enabled, string $log_path)
    {
        $this->enabled = $enabled;
        $this->log_path = $log_path;
    }

    public function handleDeprecation(array $error): void
    {
        if (!$this->enabled) {
            return;
        }

        file_put_contents(
            $this->log_path,
            sprintf('[%s] DEPRECATION: %s in %s:%d%s', date('Y-m-d H:i:s'), $error['message'], $error['file'], $error['line'], PHP_EOL),
            FILE_APPEND
        );
    }
}
