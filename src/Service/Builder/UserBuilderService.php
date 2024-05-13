<?php

declare(strict_types=1);

namespace App\Service\Builder;

use App\DTO\ManageUserDTO;
use App\Entity\User;
use App\Manager\SkillManager;
use App\Manager\UserSkillManager;
use App\Manager\UserManager;
use Exception;

readonly class UserBuilderService
{
    public function __construct(
        private UserManager $user_manager,
        private SkillManager $skill_manager,
        private UserSkillManager $user_skill_manager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function saveUserWithRelatedEntities(ManageUserDTO $user_dto): ?int
    {
        $user_id = $this->user_manager->saveUserFromDTO(new User(), $user_dto);
        $user = $this->user_manager->findUser($user_id);
        $this->addUserSkills($user, $user_dto);
        return $user_id;
    }

    /**
     * @throws Exception
     */
    public function updateUserWithRelatedEntities(User $user, ManageUserDTO $user_dto): ?int
    {
        $user_id = $this->user_manager->saveUserFromDTO($user, $user_dto);
        $this->removeUserRelatedEntities($user);
        $this->addUserSkills($user, $user_dto);
        return $user_id;
    }

    public function deleteUserWithRelatedEntities(User $user): bool
    {
        $this->removeUserRelatedEntities($user);
        $this->user_manager->deleteUser($user);
        return true;
    }

    private function removeUserRelatedEntities(User $user): void
    {
        $this->user_skill_manager->removeUserSkills($user);
    }

    /**
     * @throws Exception
     */
    private function addUserSkills(?User $user, ManageUserDTO $user_dto): void
    {
        $skills = [];
        if (!empty($user_dto->skill_ids)) {
            foreach (array_unique($user_dto->skill_ids) as $skill_id) {
                $skill = $this->skill_manager->findSkillById($skill_id);
                if ($skill === null) {
                    throw new Exception('Skill not found');
                }
                $skills[] = $skill;
            }
        }
        $this->user_skill_manager->addUserSkills($user, ...$skills);
    }
}
