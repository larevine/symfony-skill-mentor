<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\Dto\Message\StudentSkillsMessage;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StudentSkillsMessageHandler extends AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly StudentServiceInterface $student_service,
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

                // Публикуем событие об удалении навыка
                $this->domain_events_producer->publish(
                    json_encode([
                        'event' => 'student.skill_removed',
                        'payload' => [
                            'student_id' => $student->getId(),
                            'skill_id' => $skill->getSkill()->getId()
                        ]
                    ]),
                    'student.skill_removed'
                );
            }

            // Добавляем новые навыки
            foreach ($message->getSkills() as $skill_data) {
                $skill = $this->skill_service->findById(new EntityId($skill_data['skill_id']));
                if ($skill !== null) {
                    $level = new ProficiencyLevel($skill_data['level']);
                    $this->student_service->addSkill($student, $skill, $level);

                    // Публикуем событие о добавлении навыка
                    $this->domain_events_producer->publish(
                        json_encode([
                            'event' => 'student.skill_added',
                            'payload' => [
                                'student_id' => $student->getId(),
                                'skill_id' => $skill->getId(),
                                'level' => $level->getValue()
                            ]
                        ]),
                        'student.skill_added'
                    );
                } else {
                    $this->logger->warning('Skill not found', [
                        'skill_id' => $skill_data['skill_id']
                    ]);
                }
            }

            $this->logger->info('Student skills updated successfully', [
                'student_id' => $message->getStudentId()
            ]);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing student skills message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }
}
