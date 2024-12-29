<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{teacher_id}/skills/{skill_id}', methods: ['DELETE'])]
final class RemoveSkillAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(int $teacher_id, int $skill_id): JsonResponse
    {
        try {
            $teacher_id = new EntityId($teacher_id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $skill = $this->teacher_service->findSkillById(new EntityId($skill_id));
            $this->validateEntityExists($skill, 'Skill not found');

            $this->teacher_service->removeSkill($teacher, $skill);

            return $this->json(TeacherResponse::fromEntity(
                $this->teacher_service->findById($teacher_id)
            ));
        } catch (\DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
