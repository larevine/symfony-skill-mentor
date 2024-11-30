<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class TeacherSkillsDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'teacher_skills_dlx';
    }
}
