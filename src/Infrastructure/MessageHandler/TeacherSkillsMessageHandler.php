<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\TeacherSkillsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

readonly class TeacherSkillsMessageHandler implements ConsumerInterface
{
    public function __construct(
        private TeacherServiceInterface $teacher_service,
        private SkillServiceInterface $skill_service,
        private CacheItemPoolInterface $teacher_pool,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = TeacherSkillsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing teacher skills message', [
                'teacher_id' => $message->getTeacherId(),
                'skills_count' => count($message->getSkills())
            ]);

            $teacher = $this->teacher_service->findById(new EntityId($message->getTeacherId()));
            if ($teacher === null) {
                $this->logger->error('Teacher not found', [
                    'teacher_id' => $message->getTeacherId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем старые навыки
            foreach ($teacher->getSkills() as $skill) {
                $this->teacher_service->removeSkill($teacher, $skill->getSkill());
            }

            // Добавляем новые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['skill_id']));
                if ($skill !== null) {
                    $level = new ProficiencyLevel($skill_data['level']);
                    $this->teacher_service->addSkill($teacher, $skill, $level);
                } else {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['skill_id']
                    ]);
                }
            }

            // Инвалидируем кэш
            $this->teacher_pool->deleteItem('teacher_' . $message->getTeacherId());

            $this->logger->info('Teacher skills updated successfully', [
                'teacher_id' => $message->getTeacherId()
            ]);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing teacher skills message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }
}
