<?php

declare(strict_types=1);

namespace App\Service\Builder;

use App\DTO\ManageGroupDTO;
use App\Entity\Group;
use App\Manager\GroupManager;
use App\Manager\GroupUserManager;
use App\Manager\SkillManager;

readonly class GroupBuilderService
{
    public function __construct(
        private GroupManager     $group_manager,
        private SkillManager     $skill_manager,
        private GroupUserManager $group_user_manager
    ) {
    }

    /**
     * @throws \Exception
     */
    public function saveGroupWithRelatedEntities(ManageGroupDTO $group_dto): ?int
    {
        $skill = $this->skill_manager->findSkill($group_dto->skill_id);
        if (is_null($skill)) {
            return null;
        }
        $group_id = $this->group_manager->saveGroupFromDTO(new Group(), $group_dto, $skill);
        $group = $this->group_manager->findGroup($group_id);
        $this->group_user_manager->addGroupUsers($group, ...$group_dto->user_ids);
        return $group_id;
    }

    /**
     * @throws \Exception
     */
    public function updateGroupWithRelatedEntities(Group $group, ManageGroupDTO $group_dto): bool
    {
        $skill = $this->skill_manager->findSkill($group_dto->skill_id);
        if (is_null($skill)) {
            return false;
        }
        $this->group_manager->saveGroupFromDTO($group, $group_dto, $skill);
        $this->group_user_manager->removeGroupUsers($group);
        $this->group_user_manager->addGroupUsers($group, ...$group_dto->user_ids);
        return true;
    }

    public function deleteGroupWithRelatedEntities(Group $group): bool
    {
        $this->group_user_manager->removeGroupUsers($group);
        $this->group_manager->deleteGroup($group);
        return true;
    }
}
