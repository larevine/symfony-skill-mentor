<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Event\Teacher\TeacherSkillAddedEvent;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\ValueObject\SkillLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\DTO\TeacherSkillRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] TeacherSkillRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $skill_id = new EntityId($request->skill_id);
            $skill = $this->teacher_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $level = SkillLevel::fromLabel($request->level);
            $proficiency_level = ProficiencyLevel::fromInt($level->value);

            $this->teacher_service->addSkill(
                $teacher,
                $skill,
                $proficiency_level,
            );

            return $this->json(TeacherResponse::fromEntity($teacher));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
