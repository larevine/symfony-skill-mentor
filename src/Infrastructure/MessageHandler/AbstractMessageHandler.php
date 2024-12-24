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
            $result = $this->processMessage($msg);

            // Clear Doctrine's Unit of Work after each message
            $this->entity_manager->clear();

            return $result;
        } catch (\Throwable $e) {
            $this->logger->error('Error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => $msg->getBody()
            ]);

            // Clear Doctrine's Unit of Work even if there was an error
            $this->entity_manager->clear();

            return self::MSG_REJECT;
        }
    }

    /**
     * Process the message and return a ConsumerInterface status code
     */
    abstract protected function processMessage(AMQPMessage $msg): int;
}
