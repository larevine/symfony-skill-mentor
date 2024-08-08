<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Exception\UserNotFoundException;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\IGroupUserService;
use App\Domain\Entity\Group;
use App\Domain\Entity\GroupUser;
use App\Domain\Entity\User;
use App\Domain\Repository\IGroupUserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class GroupUserService implements IGroupUserService
{
    public function __construct(
        private IGroupUserRepository $group_user_repository,
        private ValidatorInterface $validator,
        private UserService $user_service,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupUsers(Group $group, int ...$user_ids): void
    {
        foreach ($user_ids as $user_id) {
            $user = $this->user_service->findUser($user_id);
            if ($user === null) {
                throw new UserNotFoundException($user_id);
            }
            if (!$this->groupUserExists($group, $user)) {
                $group_user = new GroupUser();
                $group_user->setGroup($group);
                $group_user->setUser($user);
                $user->addGroup($group_user);
                $group->addUser($group_user);
                $this->validateGroupUser($group_user);
                $this->group_user_repository->save($group_user);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroupUsers(Group $group): void
    {
        $this->group_user_repository->removeByGroup($group);
    }

    private function groupUserExists(Group $group, User $user): bool
    {
        return $this->group_user_repository->findByGroupAndUser($group, $user) !== null;
    }

    /**
     * @throws ValidationException
     */
    private function validateGroupUser(GroupUser $group_user): void
    {
        $violations = $this->validator->validate($group_user);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
