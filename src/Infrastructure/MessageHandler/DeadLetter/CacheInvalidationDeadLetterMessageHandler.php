<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class CacheInvalidationDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'cache_invalidation_dlx';
    }
}
