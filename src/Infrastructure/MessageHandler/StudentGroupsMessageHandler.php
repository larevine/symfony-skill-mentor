<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\Dto\Message\StudentGroupsMessage;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StudentGroupsMessageHandler extends AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly StudentServiceInterface $student_service,
        private readonly GroupServiceInterface $group_service,
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
            $message = StudentGroupsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing student groups message', [
                'student_id' => $message->getStudentId(),
                'groups_count' => count($message->getGroupIds())
            ]);

            $student = $this->student_service->findById(new EntityId($message->getStudentId()));
            if ($student === null) {
                $this->logger->error('Student not found', [
                    'student_id' => $message->getStudentId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем старые группы
            foreach ($student->getGroups() as $group) {
                $this->group_service->removeStudent($student, $group);

                // Публикуем событие о выходе из группы
                $this->domain_events_producer->publish(
                    json_encode([
                        'event' => 'student.left_group',
                        'payload' => [
                            'student_id' => $student->getId(),
                            'group_id' => $group->getId()
                        ]
                    ]),
                    'student.left_group'
                );
            }

            // Добавляем новые группы
            foreach ($message->getGroupIds() as $group_id) {
                $group = $this->group_service->findById(new EntityId($group_id));
                if ($group !== null) {
                    $this->group_service->addStudent($student, $group);

                    // Публикуем событие о входе в группу
                    $this->domain_events_producer->publish(
                        json_encode([
                            'event' => 'student.joined_group',
                            'payload' => [
                                'student_id' => $student->getId(),
                                'group_id' => $group->getId()
                            ]
                        ]),
                        'student.joined_group'
                    );
                } else {
                    $this->logger->warning('Group not found', [
                        'group_id' => $group_id
                    ]);
                }
            }

            $this->logger->info('Student groups updated successfully', [
                'student_id' => $message->getStudentId()
            ]);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing student groups message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }
}
