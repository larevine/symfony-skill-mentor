<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class TeacherGroupsDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'teacher_groups_dlx';
    }
}
