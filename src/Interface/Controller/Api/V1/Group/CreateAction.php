<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\CreateGroupRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups', methods: ['POST'])]
final class CreateAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SkillServiceInterface $skill_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateGroupRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($request->teacher_id);

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            // Создаем группу без студентов
            $group = $this->group_service->create(
                $request->name,
                [],  // пустой массив студентов
                $request->min_students,
                $request->max_size,
                $teacher,
            );

            // Инвалидируем кэш списка групп
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group_list',
                'id' => 'all',
            ]));

            // Добавляем требуемые навыки
            foreach ($request->required_skills as $skill_data) {
                $skill_id = new EntityId($skill_data['skill_id']);
                $skill = $this->skill_service->findById($skill_id);
                $this->validateEntityExists($skill, 'Skill not found');

                try {
                    $level = ProficiencyLevel::fromInt($skill_data['level']);
                } catch (DomainException) {
                    throw ApiException::validationError('Invalid skill level');
                }

                $this->group_service->addSkill($group, $skill, $level);
            }

            return $this->json(GroupResponse::fromEntity($group), Response::HTTP_CREATED);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
