<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class StudentSkillsDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'student_skills_dlx';
    }
}
