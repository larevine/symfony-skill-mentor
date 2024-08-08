<?php

declare(strict_types=1);

namespace App\Application\Service\Management;

use App\Application\DTO\ManageUserDTO;
use App\Application\Exception\SkillNotFoundException;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\Management\IUserManagementService;
use App\Application\Service\SkillService;
use App\Application\Service\UserService;
use App\Application\Service\UserSkillService;
use App\Domain\Entity\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

readonly class UserManagementService implements IUserManagementService
{
    public function __construct(
        private UserService $user_service,
        private SkillService $skill_service,
        private UserSkillService $user_skill_service,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function saveUserWithRelatedEntities(ManageUserDTO $user_DTO): int
    {
        $user = new User();
        $user_id = $this->user_service->saveUserFromDTO($user, $user_DTO);

        $user = $this->user_service->findUser($user_id);
        if ($user === null) {
            throw new UserNotFoundException((string)$user_id);
        }

        $this->addUserSkills($user, $user_DTO);

        return $user_id;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserWithRelatedEntities(int $user_id, ManageUserDTO $user_DTO): int
    {
        $user = $this->user_service->findUser($user_id);
        if ($user === null) {
            throw new UserNotFoundException((string)$user_id);
        }

        $user_id = $this->user_service->saveUserFromDTO($user, $user_DTO);
        $this->user_skill_service->removeUserSkills($user);
        $this->addUserSkills($user, $user_DTO);

        return $user_id;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUserWithRelatedEntities(int $user_id): bool
    {
        $user = $this->user_service->findUser($user_id);
        if ($user === null) {
            return false;
        }

        $this->user_skill_service->removeUserSkills($user);
        $this->user_service->deleteUser($user);

        return true;
    }

    /**
     * Добавление навыков пользователю
     *
     * @throws SkillNotFoundException
     * @throws ValidationException
     */
    private function addUserSkills(User $user, ManageUserDTO $user_DTO): void
    {
        foreach ($user_DTO->skill_ids as $skill_id) {
            $skill = $this->skill_service->findSkillById($skill_id);
            if ($skill === null) {
                throw new SkillNotFoundException($skill_id);
            }

            $level = $user_DTO->skill_levels[$skill_id] ?? 1;
            $this->user_skill_service->addUserSkill($user, $skill, $level);
        }
    }
}
