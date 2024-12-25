<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{student_id}/skills/{skill_id}', methods: ['DELETE'])]
final class RemoveSkillAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly SkillServiceInterface $skill_service,
    ) {
    }

    public function __invoke(int $student_id, int $skill_id): JsonResponse
    {
        try {
            $student_id = new EntityId($student_id);
            $skill_id = new EntityId($skill_id);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $skill = $this->skill_service->findById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $this->student_service->removeSkill($student, $skill);

            return $this->json(StudentResponse::fromEntity($student), Response::HTTP_OK);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
