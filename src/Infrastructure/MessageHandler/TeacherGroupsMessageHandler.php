<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\Dto\Message\TeacherGroupsMessage;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TeacherGroupsMessageHandler extends AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly TeacherServiceInterface $teacher_service,
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
            $message = TeacherGroupsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing teacher groups message', [
                'teacher_id' => $message->getTeacherId(),
                'groups_count' => count($message->getGroupIds())
            ]);

            $teacher = $this->teacher_service->findById(new EntityId($message->getTeacherId()));
            if ($teacher === null) {
                $this->logger->error('Teacher not found', [
                    'teacher_id' => $message->getTeacherId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем старые группы
            foreach ($teacher->getTeachingGroups() as $group) {
                $this->teacher_service->removeFromGroup($teacher, $group);

                // Публикуем событие об отвязке группы
                $this->domain_events_producer->publish(
                    json_encode([
                        'event' => 'group.teacher_unassigned',
                        'payload' => [
                            'teacher_id' => $teacher->getId(),
                            'group_id' => $group->getId()
                        ]
                    ]),
                    'group.teacher_unassigned'
                );
            }

            // Добавляем новые группы
            foreach ($message->getGroupIds() as $group_id) {
                $group = $this->group_service->findById(new EntityId($group_id));
                if ($group !== null) {
                    $this->teacher_service->assignToGroup($teacher, $group);

                    // Публикуем событие о привязке группы
                    $this->domain_events_producer->publish(
                        json_encode([
                            'event' => 'group.teacher_assigned',
                            'payload' => [
                                'teacher_id' => $teacher->getId(),
                                'group_id' => $group->getId()
                            ]
                        ]),
                        'group.teacher_assigned'
                    );
                } else {
                    $this->logger->warning('Group not found', [
                        'group_id' => $group_id
                    ]);
                }
            }

            $this->logger->info('Teacher groups updated successfully', [
                'teacher_id' => $message->getTeacherId()
            ]);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing teacher groups message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }
}
