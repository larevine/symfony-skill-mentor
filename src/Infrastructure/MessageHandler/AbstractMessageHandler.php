<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

abstract class AbstractMessageHandler implements ConsumerInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $entity_manager,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            return $this->processMessage($msg);
        } catch (\Throwable $e) {
            $this->logger->error('Error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => $msg->getBody()
            ]);
            return self::MSG_REJECT;
        } finally {
            // Clear Doctrine's Unit of Work cleanup even in case of database connection errors
            $this->entity_manager->clear();
        }
    }

    /**
     * Process the message and return a ConsumerInterface status code
     */
    abstract protected function processMessage(AMQPMessage $msg): int;
}
