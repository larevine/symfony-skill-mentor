<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidateHandler implements ConsumerInterface
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
        try {
            $event = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $this->logger->info('Received domain event', ['event' => $event]);

            match ($event['event']) {
                // Teacher events
                'teacher.created' => $this->handleTeacherCreated($event['payload']),
                'teacher.updated' => $this->handleTeacherUpdated($event['payload']),
                'teacher.deleted' => $this->handleTeacherDeleted($event['payload']),
                'teacher.skill_added' => $this->handleTeacherSkillAdded($event['payload']),
                'teacher.skill_removed' => $this->handleTeacherSkillRemoved($event['payload']),

                // Student events
                'student.created' => $this->handleStudentCreated($event['payload']),
                'student.updated' => $this->handleStudentUpdated($event['payload']),
                'student.deleted' => $this->handleStudentDeleted($event['payload']),
                'student.skill_added' => $this->handleStudentSkillAdded($event['payload']),
                'student.skill_removed' => $this->handleStudentSkillRemoved($event['payload']),
                'student.joined_group' => $this->handleStudentJoinedGroup($event['payload']),
                'student.left_group' => $this->handleStudentLeftGroup($event['payload']),

                // Group events
                'group.created' => $this->handleGroupCreated($event['payload']),
                'group.updated' => $this->handleGroupUpdated($event['payload']),
                'group.deleted' => $this->handleGroupDeleted($event['payload']),
                'group.skill_added' => $this->handleGroupSkillAdded($event['payload']),
                'group.skill_removed' => $this->handleGroupSkillRemoved($event['payload']),
                'group.teacher_assigned' => $this->handleGroupTeacherAssigned($event['payload']),
                'group.teacher_unassigned' => $this->handleGroupTeacherUnassigned($event['payload']),
                'group.student_added' => $this->handleGroupStudentAdded($event['payload']),
                'group.student_removed' => $this->handleGroupStudentRemoved($event['payload']),

                default => $this->logger->warning('Unknown event type', ['event' => $event]),
            };
        } catch (\JsonException $e) {
            $this->logger->error('Error processing domain event', [
                'error' => 'Syntax error',
                'event' => $msg->getBody(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('Error processing domain event', [
                'error' => $e->getMessage(),
                'event' => $msg->getBody(),
            ]);
            throw $e;
        }
    }

    // Teacher event handlers
    private function handleTeacherCreated(array $payload): void
    {
        // Инвалидируем кеш списка учителей
        $this->cache->invalidateTags(['teachers', 'teacher_' . $payload['teacher_id']]);

        $this->logger->info('Teacher created, cache invalidated', [
            'teacher_id' => $payload['teacher_id'],
        ]);
    }

    private function handleTeacherUpdated(array $payload): void
    {
        $teacher_id = $payload['teacher_id'];

        // Инвалидируем кеш конкретного учителя и список
        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id]);

        $this->logger->info('Teacher updated, cache invalidated', [
            'teacher_id' => $teacher_id,
            'changes' => $payload['teacher_info'],
        ]);
    }

    private function handleTeacherDeleted(array $payload): void
    {
        $teacher_id = $payload['teacher_id'];

        // Инвалидируем кеш конкретного учителя и список
        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id]);

        $this->logger->info('Teacher deleted, cache invalidated', [
            'teacher_id' => $teacher_id,
        ]);
    }

    private function handleTeacherSkillAdded(array $payload): void
    {
        $teacher_id = $payload['teacher_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретного учителя и список
        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id, 'skill_' . $skill_id]);

        $this->logger->info('Teacher skill added, cache invalidated', [
            'teacher_id' => $teacher_id,
            'skill_id' => $skill_id,
            'level' => $payload['level'],
        ]);
    }

    private function handleTeacherSkillRemoved(array $payload): void
    {
        $teacher_id = $payload['teacher_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретного учителя и список
        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id, 'skill_' . $skill_id]);

        $this->logger->info('Teacher skill removed, cache invalidated', [
            'teacher_id' => $teacher_id,
            'skill_id' => $skill_id,
        ]);
    }

    // Student event handlers
    private function handleStudentCreated(array $payload): void
    {
        // Инвалидируем кеш списка студентов
        $this->cache->invalidateTags(['students', 'student_' . $payload['student_id']]);

        $this->logger->info('Student created, cache invalidated', [
            'student_id' => $payload['student_id'],
        ]);
    }

    private function handleStudentUpdated(array $payload): void
    {
        $student_id = $payload['student_id'];

        // Инвалидируем кеш конкретного студента и список
        $this->cache->invalidateTags(['students', 'student_' . $student_id]);

        $this->logger->info('Student updated, cache invalidated', [
            'student_id' => $student_id,
            'changes' => $payload['student_info'],
        ]);
    }

    private function handleStudentDeleted(array $payload): void
    {
        $student_id = $payload['student_id'];

        // Инвалидируем кеш конкретного студента и список
        $this->cache->invalidateTags(['students', 'student_' . $student_id]);

        $this->logger->info('Student deleted, cache invalidated', [
            'student_id' => $student_id,
        ]);
    }

    private function handleStudentSkillAdded(array $payload): void
    {
        $student_id = $payload['student_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретного студента и список
        $this->cache->invalidateTags(['students', 'student_' . $student_id, 'skill_' . $skill_id]);

        $this->logger->info('Student skill added, cache invalidated', [
            'student_id' => $student_id,
            'skill_id' => $skill_id,
            'level' => $payload['level'],
        ]);
    }

    private function handleStudentSkillRemoved(array $payload): void
    {
        $student_id = $payload['student_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретного студента и список
        $this->cache->invalidateTags(['students', 'student_' . $student_id, 'skill_' . $skill_id]);

        $this->logger->info('Student skill removed, cache invalidated', [
            'student_id' => $student_id,
            'skill_id' => $skill_id,
        ]);
    }

    private function handleStudentJoinedGroup(array $payload): void
    {
        $student_id = $payload['student_id'];
        $group_id = $payload['group_id'];

        // Инвалидируем кеш конкретного студента и группы
        $this->cache->invalidateTags(['students', 'student_' . $student_id, 'group_' . $group_id]);

        $this->logger->info('Student joined group, cache invalidated', [
            'student_id' => $student_id,
            'group_id' => $group_id,
        ]);
    }

    private function handleStudentLeftGroup(array $payload): void
    {
        $student_id = $payload['student_id'];
        $group_id = $payload['group_id'];

        // Инвалидируем кеш конкретного студента и группы
        $this->cache->invalidateTags(['students', 'student_' . $student_id, 'group_' . $group_id]);

        $this->logger->info('Student left group, cache invalidated', [
            'student_id' => $student_id,
            'group_id' => $group_id,
        ]);
    }

    // Group event handlers
    private function handleGroupCreated(array $payload): void
    {
        // Инвалидируем кеш списка групп
        $this->cache->invalidateTags(['groups', 'group_' . $payload['group_id']]);

        $this->logger->info('Group created, cache invalidated', [
            'group_id' => $payload['group_id'],
        ]);
    }

    private function handleGroupUpdated(array $payload): void
    {
        $group_id = $payload['group_id'];

        // Инвалидируем кеш конкретной группы и список
        $this->cache->invalidateTags(['groups', 'group_' . $group_id]);

        $this->logger->info('Group updated, cache invalidated', [
            'group_id' => $group_id,
            'changes' => $payload['group_info'],
        ]);
    }

    private function handleGroupDeleted(array $payload): void
    {
        $group_id = $payload['group_id'];

        // Инвалидируем кеш конкретной группы и список
        $this->cache->invalidateTags(['groups', 'group_' . $group_id]);

        $this->logger->info('Group deleted, cache invalidated', [
            'group_id' => $group_id,
        ]);
    }

    private function handleGroupSkillAdded(array $payload): void
    {
        $group_id = $payload['group_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретной группы и список
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'skill_' . $skill_id]);

        $this->logger->info('Group skill added, cache invalidated', [
            'group_id' => $group_id,
            'skill_id' => $skill_id,
            'level' => $payload['level'],
        ]);
    }

    private function handleGroupSkillRemoved(array $payload): void
    {
        $group_id = $payload['group_id'];
        $skill_id = $payload['skill_id'];

        // Инвалидируем кеш конкретной группы и список
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'skill_' . $skill_id]);

        $this->logger->info('Group skill removed, cache invalidated', [
            'group_id' => $group_id,
            'skill_id' => $skill_id,
        ]);
    }

    private function handleGroupTeacherAssigned(array $payload): void
    {
        $group_id = $payload['group_id'];
        $teacher_id = $payload['teacher_id'];

        // Инвалидируем кеш конкретной группы и учителя
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'teacher_' . $teacher_id]);

        $this->logger->info('Group teacher assigned, cache invalidated', [
            'group_id' => $group_id,
            'teacher_id' => $teacher_id,
        ]);
    }

    private function handleGroupTeacherUnassigned(array $payload): void
    {
        $group_id = $payload['group_id'];
        $teacher_id = $payload['teacher_id'];

        // Инвалидируем кеш конкретной группы и учителя
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'teacher_' . $teacher_id]);

        $this->logger->info('Group teacher unassigned, cache invalidated', [
            'group_id' => $group_id,
            'teacher_id' => $teacher_id,
        ]);
    }

    private function handleGroupStudentAdded(array $payload): void
    {
        $group_id = $payload['group_id'];
        $student_id = $payload['student_id'];

        // Инвалидируем кеш конкретной группы и студента
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'student_' . $student_id]);

        $this->logger->info('Group student added, cache invalidated', [
            'group_id' => $group_id,
            'student_id' => $student_id,
        ]);
    }

    private function handleGroupStudentRemoved(array $payload): void
    {
        $group_id = $payload['group_id'];
        $student_id = $payload['student_id'];

        // Инвалидируем кеш конкретной группы и студента
        $this->cache->invalidateTags(['groups', 'group_' . $group_id, 'student_' . $student_id]);

        $this->logger->info('Group student removed, cache invalidated', [
            'group_id' => $group_id,
            'student_id' => $student_id,
        ]);
    }
}
