<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\AddSkillRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{group_id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly SkillServiceInterface $skill_service,
    ) {
    }

    public function __invoke(
        int $group_id,
        #[MapRequestPayload] AddSkillRequest $request,
    ): JsonResponse {
        try {
            $group_id = new EntityId($group_id);
            $skill_id = new EntityId($request->skill_id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $skill = $this->skill_service->findById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            try {
                $level = ProficiencyLevel::fromInt($request->level);
            } catch (DomainException) {
                throw ApiException::validationError('Invalid skill level');
            }

            $this->group_service->addSkill($group, $skill, $level);

            return $this->json(GroupResponse::fromEntity($group), Response::HTTP_OK);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
