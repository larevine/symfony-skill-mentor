<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{student_id}/groups/{group_id}', methods: ['DELETE'])]
final class LeaveGroupAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(
        int $student_id,
        int $group_id,
    ): JsonResponse {
        try {
            $student = $this->student_service->findById(new EntityId($student_id));
            $this->validateEntityExists($student, 'Student not found');

            $group = $this->group_service->findById(new EntityId($group_id));
            $this->validateEntityExists($group, 'Group not found');

            $this->student_service->leaveGroup($student, $group);
            $this->group_service->removeStudent($group, $student);

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
