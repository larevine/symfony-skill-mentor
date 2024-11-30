<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{id}/skills/{skillId}', methods: ['DELETE'])]
final class RemoveSkillAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(int $id, int $skillId): JsonResponse
    {
        try {
            $teacher_id = new EntityId($id);
            $skill_id = new EntityId($skillId);

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $skill = $this->teacher_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $this->teacher_service->removeSkill($teacher, $skill);

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
