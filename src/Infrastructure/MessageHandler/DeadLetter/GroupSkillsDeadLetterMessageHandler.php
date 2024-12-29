<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

class GroupSkillsDeadLetterMessageHandler extends AbstractDeadLetterMessageHandler
{
    protected static function getQueueName(): string
    {
        return 'group_skills_dlx';
    }
}
