<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\Dto\Message\TeacherGroupsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

readonly class TeacherGroupsMessageHandler implements ConsumerInterface
{
    public function __construct(
        private TeacherServiceInterface $teacher_service,
        private GroupServiceInterface $group_service,
        private CacheItemPoolInterface $teacher_pool,
        private CacheItemPoolInterface $group_pool,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = TeacherGroupsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing teacher groups message', [
                'teacher_id' => $message->getTeacherId(),
                'group_count' => count($message->getGroupIds())
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
            }

            // Добавляем новые группы
            foreach ($message->getGroupIds() as $group_id) {
                $group = $this->group_service->findById(new EntityId($group_id));
                if ($group !== null) {
                    if ($teacher->hasRequiredSkills($group)) {
                        $this->teacher_service->assignToGroup($teacher, $group);
                    } else {
                        $this->logger->warning('Teacher does not have required skills for group', [
                            'teacher_id' => $message->getTeacherId(),
                            'group_id' => $group_id
                        ]);
                    }
                } else {
                    $this->logger->warning('Group not found', [
                        'group_id' => $group_id
                    ]);
                }
            }

            // Инвалидируем кэш
            $this->teacher_pool->deleteItem('teacher_' . $message->getTeacherId());
            foreach ($message->getGroupIds() as $group_id) {
                $this->group_pool->deleteItem('group_' . $group_id);
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
