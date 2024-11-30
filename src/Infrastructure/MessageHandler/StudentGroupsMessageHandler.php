<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\Dto\Message\StudentGroupsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

readonly class StudentGroupsMessageHandler implements ConsumerInterface
{
    public function __construct(
        private StudentServiceInterface $student_service,
        private GroupServiceInterface $group_service,
        private CacheItemPoolInterface $student_pool,
        private CacheItemPoolInterface $group_pool,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = StudentGroupsMessage::fromArray(json_decode($msg->getBody(), true));

            $this->logger->info('Processing student groups message', [
                'student_id' => $message->getStudentId(),
                'group_count' => count($message->getGroupIds())
            ]);

            $student = $this->student_service->findById(new EntityId($message->getStudentId()));
            if ($student === null) {
                $this->logger->error('Student not found', [
                    'student_id' => $message->getStudentId()
                ]);
                return self::MSG_REJECT;
            }

            // Удаляем студента из всех групп
            foreach ($student->getGroups() as $group) {
                $this->group_service->removeStudent($group, $student);
            }

            // Добавляем студента в новые группы
            foreach ($message->getGroupIds() as $group_id) {
                $group = $this->group_service->findById(new EntityId($group_id));
                if ($group === null) {
                    $this->logger->warning('Group not found', [
                        'group_id' => $group_id
                    ]);
                    continue;
                }

                $this->group_service->addStudent($group, $student);
            }

            // Инвалидируем кэш
            $this->student_pool->deleteItem('student_' . $message->getStudentId());
            foreach ($message->getGroupIds() as $group_id) {
                $this->group_pool->deleteItem('group_' . $group_id);
            }

            return self::MSG_ACK;
        } catch (\Throwable $e) {
            $this->logger->error('Error processing student groups message: ' . $e->getMessage());
            return self::MSG_REJECT;
        }
    }
}
