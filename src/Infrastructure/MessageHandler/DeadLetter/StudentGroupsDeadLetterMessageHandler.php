<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class StudentGroupsDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'student_groups_dlx';
    }
}
