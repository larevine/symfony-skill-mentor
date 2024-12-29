<?php

namespace App\Infrastructure\Logger;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;

class DeprecationErrorHandler extends ErrorHandler
{
    private LoggerInterface $deprecationLogger;

    public function __construct(LoggerInterface $deprecationLogger)
    {
        parent::__construct();
        $this->deprecationLogger = $deprecationLogger;
    }

    public function handleError(int $type, string $message, string $file, int $line): bool
    {
        if ($type === E_DEPRECATED || $type === E_USER_DEPRECATED) {
            $this->deprecationLogger->warning($message, [
                'type' => $type,
                'file' => $file,
                'line' => $line,
            ]);
            return true;
        }

        return parent::handleError($type, $message, $file, $line);
    }
}
