<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\TeacherSkillsMessage;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TeacherSkillsMessageHandler extends AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SkillServiceInterface $skill_service,
        private readonly ProducerInterface $domain_events_producer,
    ) {
        parent::__construct($entity_manager, $logger);
    }

    public function execute(AMQPMessage $msg): int
    {
        return $this->processMessage($msg);
    }

    protected function processMessage(AMQPMessage $msg): int
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

                // Публикуем событие об удалении навыка
                $this->domain_events_producer->publish(
                    json_encode([
                        'event' => 'teacher.skill_removed',
                        'payload' => [
                            'teacher_id' => $teacher->getId(),
                            'skill_id' => $skill->getSkill()->getId()
                        ]
                    ]),
                    'teacher.skill_removed'
                );
            }

            // Добавляем новые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['skill_id']));
                if ($skill !== null) {
                    $level = new ProficiencyLevel($skill_data['level']);
                    $this->teacher_service->addSkill($teacher, $skill, $level);

                    // Публикуем событие о добавлении навыка
                    $this->domain_events_producer->publish(
                        json_encode([
                            'event' => 'teacher.skill_added',
                            'payload' => [
                                'teacher_id' => $teacher->getId(),
                                'skill_id' => $skill->getId(),
                                'level' => $level->getValue()
                            ]
                        ]),
                        'teacher.skill_added'
                    );
                } else {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['skill_id']
                    ]);
                }
            }

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
