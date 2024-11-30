<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\DTO\UpdateSkillProficiencyRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateSkillProficiencyRequest $request,
    ): JsonResponse {
        try {
            $group_id = new EntityId($id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $skill = $this->group_service->findSkillById(new EntityId($request->skill_id));
            $this->validateEntityExists($skill, 'Skill not found');

            try {
                $level = ProficiencyLevel::fromInt($request->level);
            } catch (DomainException) {
                throw ApiException::validationError('Invalid skill level');
            }

            $this->group_service->addSkill($group, $skill, $level);

            // Инвалидируем кэш группы
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group',
                'id' => $id,
            ]));

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
