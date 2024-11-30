<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}/groups/{groupId}', methods: ['DELETE'])]
final class LeaveGroupAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly GroupServiceInterface $group_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(int $id, int $groupId): JsonResponse
    {
        try {
            $student_id = new EntityId($id);
            $group_id = new EntityId($groupId);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            // Check if student is actually in this group
            if (!$student->getGroups()->contains($group)) {
                throw ApiException::validationError('Student is not in this group');
            }

            $this->group_service->removeStudent($group, $student);

            // Инвалидируем кэш студента и группы
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'student',
                'id' => $id,
            ]));
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group',
                'id' => $groupId,
            ]));

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
