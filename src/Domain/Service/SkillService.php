<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Skill;
use App\Domain\Exception\SkillException;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\Service\SkillServiceInterface;
use DomainException;

readonly class SkillService implements SkillServiceInterface
{
    public function __construct(
        private SkillRepositoryInterface $skill_repository,
    ) {
    }

    public function findById(EntityId $id): ?Skill
    {
        return $this->skill_repository->findById($id->getValue());
    }

    /**
     * @return array<Skill>
     */
    public function findAll(): array
    {
        return $this->skill_repository->findAll();
    }

    public function findByName(string $name): ?Skill
    {
        return $this->skill_repository->findByName($name);
    }

    public function createSkill(string $name, ?string $description = null): Skill
    {
        try {
            $existing_skill = $this->skill_repository->findByName($name);
            if ($existing_skill !== null) {
                throw new DomainException('Skill with this name already exists');
            }

            $skill = new Skill($name, $description);
            $this->skill_repository->save($skill);

            return $skill;
        } catch (DomainException $e) {
            throw SkillException::fromDomainException($e);
        }
    }

    public function deleteSkill(Skill $skill): void
    {
        try {
            $this->skill_repository->remove($skill);
        } catch (DomainException $e) {
            throw SkillException::fromDomainException($e);
        }
    }
}
