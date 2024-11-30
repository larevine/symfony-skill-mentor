<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Psr\Log\LoggerInterface;

readonly class CacheInvalidationMessageHandler implements ConsumerInterface
{
    public function __construct(
        private TagAwareAdapterInterface $teacher_pool,
        private TagAwareAdapterInterface $student_pool,
        private TagAwareAdapterInterface $group_pool,
        private LoggerInterface $logger
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        $this->logger->debug('Received cache invalidation message: ' . $msg->getBody());
        $data = json_decode($msg->getBody(), true);
        if (!isset($data['type'])) {
            $this->logger->error('Invalid cache invalidation message: type not set');
            return ConsumerInterface::MSG_REJECT;
        }

        try {
            switch ($data['type']) {
                case 'teacher':
                    $this->logger->debug('Invalidating cache for teacher ' . $data['id']);
                    $this->teacher_pool->invalidateTags(['teacher_' . $data['id']]);
                    break;
                case 'teacher_list':
                    $this->logger->debug('Invalidating cache for teacher list');
                    $this->teacher_pool->invalidateTags(['teachers']);
                    break;
                case 'student':
                    if (!isset($data['id'])) {
                        $this->logger->error('Invalid cache invalidation message: id not set for student');
                        return ConsumerInterface::MSG_REJECT;
                    }
                    $this->logger->debug('Invalidating cache for student ' . $data['id']);
                    $this->student_pool->invalidateTags(['student_' . $data['id']]);
                    break;
                case 'student_list':
                    $this->logger->debug('Invalidating cache for student list');
                    $this->student_pool->invalidateTags(['students']);
                    break;
                case 'group':
                    if (!isset($data['id'])) {
                        $this->logger->error('Invalid cache invalidation message: id not set for group');
                        return ConsumerInterface::MSG_REJECT;
                    }
                    $this->logger->debug('Invalidating cache for group ' . $data['id']);
                    $this->group_pool->invalidateTags(['group_' . $data['id']]);
                    break;
                case 'group_list':
                    $this->logger->debug('Invalidating cache for group list');
                    $this->group_pool->invalidateTags(['groups']);
                    break;
                default:
                    $this->logger->error('Invalid cache invalidation message: unknown type ' . $data['type']);
                    return ConsumerInterface::MSG_REJECT;
            }
            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing cache invalidation message: ' . $e->getMessage(), [
                'exception' => $e,
                'message' => $msg->getBody()
            ]);
            return ConsumerInterface::MSG_REJECT_REQUEUE;
        }
    }
}
