<?php

declare(strict_types=1);

namespace App\Application\Service\Management;

use App\Application\DTO\ManageGroupDTO;
use App\Application\Exception\GroupNotFoundException;
use App\Application\Exception\SkillNotFoundException;
use App\Application\Interface\Service\Management\IGroupManagementService;
use App\Application\Service\GroupService;
use App\Application\Service\GroupUserService;
use App\Application\Service\SkillService;
use App\Domain\Entity\Group;

readonly class GroupManagementService implements IGroupManagementService
{
    public function __construct(
        private GroupService $group_service,
        private SkillService $skill_service,
        private GroupUserService $group_user_service
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function saveGroupWithRelatedEntities(ManageGroupDTO $group_dto): int
    {
        $skill = $this->skill_service->findSkillById($group_dto->skill_id);
        if (is_null($skill)) {
            throw new SkillNotFoundException($group_dto->skill_id);
        }

        $group = $this->group_service->createGroupFromDTO(new Group(), $group_dto, $skill);
        $this->group_user_service->addGroupUsers($group, ...$group_dto->user_ids);

        return $group->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroupWithRelatedEntities(int $group_id, ManageGroupDTO $group_dto): bool
    {
        $group = $this->group_service->findGroup($group_id);
        if (is_null($group)) {
            throw new GroupNotFoundException($group_id);
        }

        $skill = $this->skill_service->findSkillById($group_dto->skill_id);
        if (is_null($skill)) {
            throw new SkillNotFoundException($group_dto->skill_id);
        }

        $this->group_service->updateGroupFromDTO($group, $group_dto, $skill);

        $this->group_user_service->removeGroupUsers($group);
        $this->group_user_service->addGroupUsers($group, ...$group_dto->user_ids);

        return true;
    }

    /**
     * Удаление группы со связанными сущностями (пользователями)
     *
     * @throws GroupNotFoundException
     */
    public function deleteGroupWithRelatedEntities(int $groupId): bool
    {
        $group = $this->group_service->findGroup($groupId);
        if (is_null($group)) {
            throw new GroupNotFoundException($groupId);
        }

        $this->group_user_service->removeGroupUsers($group);
        $this->group_service->deleteGroup($group);

        return true;
    }
}
