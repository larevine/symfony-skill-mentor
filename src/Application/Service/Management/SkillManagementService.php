<?php

declare(strict_types=1);

namespace App\Application\Service\Management;

use App\Application\DTO\ManageSkillDTO;
use App\Application\Exception\SkillNotFoundException;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\Management\ISkillManagementService;
use App\Application\Service\SkillService;
use App\Domain\Entity\Skill;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class SkillManagementService implements ISkillManagementService
{
    public function __construct(
        private SkillService $skill_service,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function saveSkillWithRelatedEntities(ManageSkillDTO $dto): int
    {
        $skill = new Skill();
        $skill->setName($dto->name);
        $skill->setLevel($dto->level);
        $this->validateSkill($skill);
        $this->skill_service->saveSkill($skill);

        return $skill->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateSkillWithRelatedEntities(int $skill_id, ManageSkillDTO $dto): bool
    {
        $skill = $this->skill_service->findSkillById($skill_id);
        if ($skill === null) {
            throw new SkillNotFoundException($skill_id);
        }

        $skill->setName($dto->name);
        $skill->setLevel($dto->level);
        $this->validateSkill($skill);

        $this->skill_service->saveSkill($skill);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSkillWithRelatedEntities(int $skill_id): bool
    {
        $skill = $this->skill_service->findSkillById($skill_id);
        if ($skill === null) {
            throw new SkillNotFoundException($skill_id);
        }

        $this->skill_service->deleteSkill($skill);

        return true;
    }

    /**
     * @throws ValidationException
     */
    private function validateSkill(Skill $skill): void
    {
        $violations = $this->validator->validate($skill);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
