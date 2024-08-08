<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\ISkillService;
use App\Domain\Entity\Skill;
use App\Domain\Repository\ISkillRepository;
use App\Domain\ValueObject\SkillLevelEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class SkillService implements ISkillService
{
    public function __construct(
        private ISkillRepository $skill_repository,
        private ValidatorInterface $validator,
    ) {
    }

    public function findSkillById(int $id): ?Skill
    {
        return $this->skill_repository->find($id);
    }

    public function findSkillByName(string $name): ?Skill
    {
        return $this->skill_repository->findByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function createSkill(string $name, int $level = 1): Skill
    {
        $skill = new Skill();
        $skill->setName($name);
        $skill->setLevel(SkillLevelEnum::tryFrom($level));
        $this->validateSkill($skill);
        $this->skill_repository->save($skill);
        return $skill;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSkill(Skill $skill, string $name, int $level): void
    {
        $skill->setName($name);
        $skill->setLevel(SkillLevelEnum::tryFrom($level));
        $this->validateSkill($skill);
        $this->skill_repository->save($skill);
    }

    public function saveSkill(Skill $skill): void
    {
        $this->skill_repository->save($skill);
    }

    public function deleteSkill(Skill $skill): void
    {
        $this->skill_repository->delete($skill);
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginated(int $page, int $per_page): array
    {
        return $this->skill_repository->findPaginated($page, $per_page);
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
