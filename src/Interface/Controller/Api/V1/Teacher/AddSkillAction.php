<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Domain\ValueObject\SkillLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\DTO\TeacherSkillRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
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
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] TeacherSkillRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($id);
            $skill_id = new EntityId($request->skill_id);
            $level = SkillLevel::fromLabel($request->level);

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $skill = $this->teacher_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            try {
                $proficiency_level = ProficiencyLevel::fromInt($level->value);
            } catch (DomainException) {
                throw ApiException::validationError('Invalid skill level');
            }

            $this->teacher_service->addSkill(
                $teacher,
                $skill,
                $proficiency_level,
            );

            // Инвалидируем кэш учителя
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'teacher',
                'id' => $id,
            ]));

            return $this->json(TeacherResponse::fromEntity($teacher));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
