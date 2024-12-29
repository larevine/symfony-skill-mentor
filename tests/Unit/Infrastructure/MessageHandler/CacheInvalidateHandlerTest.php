<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\MessageHandler;

use App\Domain\Event\Teacher\TeacherCreatedEvent;
use App\Domain\Event\Teacher\TeacherDeletedEvent;
use App\Domain\Event\Teacher\TeacherSkillAddedEvent;
use App\Domain\Event\Teacher\TeacherSkillRemovedEvent;
use App\Domain\Event\Teacher\TeacherUpdatedEvent;
use App\Infrastructure\MessageHandler\CacheInvalidateHandler;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidateHandlerTest extends TestCase
{
    private TagAwareCacheInterface $cache;
    private LoggerInterface $logger;
    private CacheInvalidateHandler $handler;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new CacheInvalidateHandler($this->cache, $this->logger);
    }

    public function testTeacherCreatedEventInvalidatesCache(): void
    {
        // Arrange
        $teacher_id = 123;
        $event = new TeacherCreatedEvent($teacher_id, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $message = new AMQPMessage(json_encode($event));

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['teachers', 'teacher_' . $teacher_id]);

        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received domain event',
                    self::callback(function (array $context) {
                        $event_data = $context['event'];
                        return isset($event_data['event']) &&
                               isset($event_data['payload']) &&
                               $event_data['event'] === 'teacher.created';
                    })
                ],
                [
                    'Teacher created, cache invalidated',
                    ['teacher_id' => $teacher_id]
                ]
            );

        // Act
        $this->handler->execute($message);
    }

    public function testTeacherUpdatedEventInvalidatesCache(): void
    {
        // Arrange
        $teacher_id = 123;
        $body = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ];
        $event = new TeacherUpdatedEvent($teacher_id, $body);
        $message = new AMQPMessage(json_encode($event));

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['teachers', 'teacher_' . $teacher_id]);

        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received domain event',
                    self::callback(function (array $context) {
                        $payload = $context['event'];
                        return isset($payload['event']) &&
                               isset($payload['payload']) &&
                               $payload['event'] === 'teacher.updated';
                    })
                ],
                [
                    'Teacher updated, cache invalidated',
                    [
                        'teacher_id' => $teacher_id,
                        'changes' => $body
                    ]
                ]
            );

        // Act
        $this->handler->execute($message);
    }

    public function testTeacherDeletedEventInvalidatesCache(): void
    {
        // Arrange
        $teacher_id = 123;
        $event = new TeacherDeletedEvent($teacher_id);

        $message = new AMQPMessage(json_encode($event));

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['teachers', 'teacher_' . $teacher_id]);

        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received domain event',
                    self::callback(function (array $context) {
                        $payload = $context['event'];
                        return isset($payload['event']) &&
                               isset($payload['payload']) &&
                               $payload['event'] === 'teacher.deleted';
                    })
                ],
                [
                    'Teacher deleted, cache invalidated',
                    ['teacher_id' => $teacher_id]
                ]
            );

        // Act
        $this->handler->execute($message);
    }

    public function testTeacherSkillAddedEventInvalidatesCache(): void
    {
        // Arrange
        $teacher_id = 123;
        $skill_id = 456;
        $event = new TeacherSkillAddedEvent($teacher_id, $skill_id, 'level_5');

        $message = new AMQPMessage(json_encode($event));

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['teachers', 'teacher_' . $teacher_id, 'skill_' . $skill_id]);

        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received domain event',
                    self::callback(function (array $context) {
                        $payload = $context['event'];
                        return isset($payload['event']) &&
                               isset($payload['payload']) &&
                               $payload['event'] === 'teacher.skill_added';
                    })
                ],
                [
                    'Teacher skill added, cache invalidated',
                    [
                        'teacher_id' => $teacher_id,
                        'skill_id' => $skill_id,
                        'level' => 'level_5'
                    ]
                ]
            );

        // Act
        $this->handler->execute($message);
    }

    public function testTeacherSkillRemovedEventInvalidatesCache(): void
    {
        // Arrange
        $teacher_id = 123;
        $skill_id = 456;
        $event = new TeacherSkillRemovedEvent($teacher_id, $skill_id);

        $message = new AMQPMessage(json_encode($event));

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(['teachers', 'teacher_' . $teacher_id, 'skill_' . $skill_id]);

        $this->logger
            ->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received domain event',
                    self::callback(function (array $context) {
                        $payload = $context['event'];
                        return isset($payload['event']) &&
                               isset($payload['payload']) &&
                               $payload['event'] === 'teacher.skill_removed';
                    })
                ],
                [
                    'Teacher skill removed, cache invalidated',
                    [
                        'teacher_id' => $teacher_id,
                        'skill_id' => $skill_id
                    ]
                ]
            );

        // Act
        $this->handler->execute($message);
    }
}
