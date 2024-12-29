<?php

namespace App\Infrastructure\MessageHandler\DeadLetter;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

abstract class AbstractDeadLetterMessageHandler implements ConsumerInterface
{
    public function __construct(
        protected readonly LoggerInterface $logger
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $data = json_decode($msg->getBody(), true);
            $headers = $msg->get_properties();

            // Логируем информацию о неуспешном сообщении
            $this->logger->error('Dead letter message received', [
                'queue' => static::getQueueName(),
                'data' => $data,
                'headers' => $headers,
                'death_count' => $headers['x-death'][0]['count'] ?? 0,
                'original_exchange' => $headers['x-death'][0]['exchange'] ?? 'unknown',
                'reason' => $headers['x-death'][0]['reason'] ?? 'unknown'
            ]);

            return self::MSG_ACK; // Всегда подтверждаем DLQ сообщения
        } catch (\Exception $e) {
            $this->logger->critical('Error processing dead letter message', [
                'queue' => static::getQueueName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::MSG_REJECT;
        }
    }

    abstract protected static function getQueueName(): string;
}
