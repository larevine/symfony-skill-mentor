<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ManageSkillDTO;
use App\Entity\Enum\SkillLevel;
use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SkillManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function clearEntityManager(): void
    {
        $this->em->clear();
    }

    public function findSkill(int $id): ?Skill
    {
        $repository = $this->em->getRepository(Skill::class);
        $skill = $repository->find($id);

        return $skill instanceof Skill ? $skill : null;
    }

    public function findSkillById(int $id): ?Skill
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);
        /** @var Skill|null $skill */
        return $skill_repository->find($id);
    }

    public function findSkillByName(string $name): ?Skill
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);
        /** @var Skill|null $skill */
        return $skill_repository->findOneBy(['name' => $name]);
    }

    public function findSkillByLevel(SkillLevel $level): ?Skill
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);
        /** @var Skill|null $skill */
        return $skill_repository->findOneBy(['level' => $level]);
    }

    public function findSkillByNameAndLevel(string $name, SkillLevel $level): ?Skill
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);
        /** @var Skill|null $skill */
        return $skill_repository->findOneBy(['name' => $name, 'level' => $level]);
    }

    /**
     * @return Skill[]
     */
    public function getSkills(int $page, int $per_page): array
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);

        return $skill_repository->getSkills($page, $per_page);
    }

    public function saveSkill(Skill $skill): void
    {
        $this->em->persist($skill);
        $this->em->flush();
    }

    public function saveSkillFromDTO(Skill $skill, ManageSkillDTO $dto): ?int
    {
        $skill->setName($dto->name);
        $this->em->persist($skill);
        $this->em->flush();

        return $skill->getId();
    }

    public function createByNameAndLevel(string $name, SkillLevel $level): Skill
    {
        $skill = new Skill();
        $skill->setName($name);
        $skill->setLevel($level);
        $this->em->persist($skill);
        $this->em->flush();

        return $skill;
    }

    public function updateSkillNameById(int $skill_id, string $name): ?Skill
    {
        $skill = $this->findSkill($skill_id);
        if (!($skill instanceof Skill)) {
            return null;
        }
        $skill->setName($name);
        $this->em->flush();

        return $skill;
    }

    public function updateSkillLevelById(int $skill_id, SkillLevel $level): ?Skill
    {
        $skill = $this->findSkill($skill_id);
        if (!($skill instanceof Skill)) {
            return null;
        }
        $skill->setLevel($level);
        $this->em->flush();

        return $skill;
    }

    public function updateSkillName(Skill $skill, string $name): void
    {
        $skill->setName($name);
        $this->em->flush();
    }

    public function updateSkillLevel(Skill $skill, SkillLevel $level): void
    {
        $skill->setLevel($level);
        $this->em->flush();
    }

    public function deleteSkill(Skill $skill): bool
    {
        $this->em->remove($skill);
        $this->em->flush();

        return true;
    }

    public function deleteSkillById(int $skillId): bool
    {
        /** @var SkillRepository $skill_repository */
        $skill_repository = $this->em->getRepository(Skill::class);
        /** @var Skill $skill */
        $skill = $skill_repository->find($skillId);
        if ($skill === null) {
            return false;
        }
        return $this->deleteSkill($skill);
    }
}
