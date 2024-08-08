<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\ManageGroupDTO;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\IGroupService;
use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Repository\IGroupRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class GroupService implements IGroupService
{
    public function __construct(
        private IGroupRepository $group_repository,
        private ValidatorInterface $validator,
    ) {
    }

    public function findGroup(int $id): ?Group
    {
        return $this->group_repository->find($id);
    }

    public function saveGroup(Group $group): void
    {
        $this->group_repository->save($group);
    }

    public function deleteGroup(Group $group): void
    {
        $this->group_repository->delete($group);
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginated(int $page, int $per_page): array
    {
        return $this->group_repository->findPaginated($page, $per_page);
    }

    /**
     * {@inheritdoc}
     */
    public function createGroupFromDTO(Group $group, ManageGroupDTO $group_dto, Skill $skill): Group
    {
        $group->setName($group_dto->name);
        $group->setSkill($skill);

        $this->validateGroup($group);

        $this->group_repository->save($group);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroupFromDTO(Group $group, ManageGroupDTO $group_dto, Skill $skill): Group
    {
        $group->setName($group_dto->name);
        $group->setSkill($skill);

        $this->validateGroup($group);

        $this->group_repository->save($group);

        return $group;
    }

    /**
     * @throws ValidationException
     */
    private function validateGroup(Group $group): void
    {
        $violations = $this->validator->validate($group);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
