<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Psr\Log\LoggerInterface;

class CacheInvalidationMessageHandler extends AbstractMessageHandler
{
    public function __construct(
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        private readonly TagAwareAdapterInterface $teacher_pool,
        private readonly TagAwareAdapterInterface $student_pool,
        private readonly TagAwareAdapterInterface $group_pool,
    ) {
        parent::__construct($entity_manager, $logger);
    }

    public function execute(AMQPMessage $msg): int
    {
        return $this->processMessage($msg);
    }

    protected function processMessage(AMQPMessage $msg): int
    {
        $this->logger->debug('Received cache invalidation message: ' . $msg->getBody());
        $data = json_decode($msg->getBody(), true);
        if (!isset($data['type'])) {
            $this->logger->error('Invalid cache invalidation message: type not set');
            return self::MSG_REJECT;
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
                        return self::MSG_REJECT;
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
                        return self::MSG_REJECT;
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
                    return self::MSG_REJECT;
            }
            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->logger->error('Error processing cache invalidation message: ' . $e->getMessage(), [
                'exception' => $e,
                'message' => $msg->getBody()
            ]);
            return self::MSG_REJECT_REQUEUE;
        }
    }
}
