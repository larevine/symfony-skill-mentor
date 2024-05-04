<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ManageGroupDTO;
use App\Entity\Enum\SkillLevel;
use App\Entity\Group;
use App\Entity\Skill;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class GroupManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findGroup(int $id): ?Group
    {
        $repository = $this->em->getRepository(Group::class);
        $group = $repository->find($id);

        return $group instanceof Group ? $group : null;
    }

    public function findGroupByName(string $name): ?Group
    {
        /** @var GroupRepository $group_repository */
        $group_repository = $this->em->getRepository(Group::class);
        /** @var Group|null $group */
        $group = $group_repository->findOneBy(['name' => $name]);

        return $group;
    }

    /**
     * @return Group[]
     */
    public function getGroups(int $page, int $per_page): array
    {
        /** @var GroupRepository $group_repository */
        $group_repository = $this->em->getRepository(Group::class);

        return $group_repository->getGroups($page, $per_page);
    }

    public function saveGroup(Group $group): void
    {
        $this->em->persist($group);
        $this->em->flush();
    }

    public function saveGroupFromDTO(Group $group, ManageGroupDTO $manage_group_DTO, Skill $skill): ?int
    {
        $group->setName($manage_group_DTO->name);
        $group->setLimitStudents($manage_group_DTO->limit_students);
        $group->setLimitTeachers($manage_group_DTO->limit_teachers);
        $group->setSkill($skill);
        $group->setLevel(SkillLevel::fromString($manage_group_DTO->level));
        $this->em->persist($group);
        $this->em->flush();

        return $group->getId();
    }

    public function deleteGroup(Group $group): bool
    {
        $this->em->remove($group);
        $this->em->flush();

        return true;
    }

    public function deleteGroupById(int $group_id): bool
    {
        /** @var GroupRepository $group_repository */
        $group_repository = $this->em->getRepository(Group::class);
        /** @var Group $group */
        $group = $group_repository->find($group_id);
        if ($group === null) {
            return false;
        }
        return $this->deleteGroup($group);
    }
}
