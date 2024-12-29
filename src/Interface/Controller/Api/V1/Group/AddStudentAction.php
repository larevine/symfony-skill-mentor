<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{group_id}/students/{student_id}', methods: ['POST'])]
final class AddStudentAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(
        int $group_id,
        int $student_id,
    ): JsonResponse {
        try {
            $group = $this->group_service->findById(new EntityId($group_id));
            $this->validateEntityExists($group, 'Group not found');

            $student = $this->student_service->findById(new EntityId($student_id));
            $this->validateEntityExists($student, 'Student not found');

            $this->student_service->joinGroup($student, $group);
            $this->group_service->addStudent($group, $student);

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
