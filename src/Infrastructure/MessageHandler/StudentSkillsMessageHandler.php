<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\StudentSkillsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

readonly class StudentSkillsMessageHandler implements ConsumerInterface
{
    public function __construct(
        private StudentServiceInterface $student_service,
        private SkillServiceInterface $skill_service,
        private CacheItemPoolInterface $student_pool,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = StudentSkillsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing student skills message', [
                'student_id' => $message->getStudentId(),
                'skills_count' => count($message->getSkills())
            ]);

            $student = $this->student_service->findById(new EntityId($message->getStudentId()));
            if ($student === null) {
                $this->logger->error('Student not found', [
                    'student_id' => $message->getStudentId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем старые навыки
            foreach ($student->getSkills() as $skill) {
                $this->student_service->removeSkill($student, $skill->getSkill());
            }

            // Добавляем новые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['id']));
                if ($skill === null) {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['id']
                    ]);
                    continue;
                }

                $this->student_service->addSkill(
                    $student,
                    $skill,
                    ProficiencyLevel::fromInt($skill_data['level'])
                );
            }

            // Инвалидируем кэш
            $this->student_pool->deleteItem('student_' . $message->getStudentId());

            return self::MSG_ACK;
        } catch (\Throwable $e) {
            $this->logger->error('Error processing student skills message: ' . $e->getMessage());
            return self::MSG_REJECT;
        }
    }
}
