<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}/teacher/{teacher_id}', methods: ['PUT'])]
final class AssignTeacherAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TeacherServiceInterface $teacher_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(int $id, int $teacher_id): JsonResponse
    {
        try {
            $group_id = new EntityId($id);
            $teacher_id = new EntityId($teacher_id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            // Используем оба сервиса для поддержания консистентности
            $this->teacher_service->assignToGroup($teacher, $group);
            $this->group_service->assignTeacher($group, $teacher);

            // Инвалидируем кэш группы и учителя
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group',
                'id' => $id,
            ]));
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'teacher',
                'id' => $teacher_id,
            ]));

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
