<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ManageUserDTO;
use App\Entity\Enum\UserStatus;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findUser(int $id): ?User
    {
        $repository = $this->em->getRepository(User::class);
        $user = $repository->find($id);

        return $user instanceof User ? $user : null;
    }

    public function findUserByEmail(string $email): ?User
    {
        /** @var UserRepository $user_repository */
        $user_repository = $this->em->getRepository(User::class);
        /** @var User|null $user */
        $user = $user_repository->findOneBy(['email' => $email]);

        return $user;
    }

    public function findUserByToken(string $token): ?User
    {
        /** @var UserRepository $user_repository */
        $user_repository = $this->em->getRepository(User::class);
        /** @var User|null $user */
        return $user_repository->findOneBy(['token' => $token]);
    }

    /**
     * @return User[]
     */
    public function getUsers(int $page, int $per_page): array
    {
        /** @var UserRepository $user_repository */
        $user_repository = $this->em->getRepository(User::class);

        return $user_repository->getUsers($page, $per_page);
    }

    public function saveUser(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function saveUserFromDTO(User $user, ManageUserDTO $manage_user_DTO): ?int
    {
        $user->setEmail($manage_user_DTO->email);
        $user->setName($manage_user_DTO->name);
        $user->setSurname($manage_user_DTO->surname);
        $user->setStatus(UserStatus::fromString($manage_user_DTO->status));
        $user->setUpdatedAt();
        $this->em->persist($user);
        $this->em->flush();

        return $user->getId();
    }

    public function deleteUser(User $user): bool
    {
        $this->em->remove($user);
        $this->em->flush();

        return true;
    }

    public function deleteUserById(int $user_id): bool
    {
        /** @var UserRepository $user_repository */
        $user_repository = $this->em->getRepository(User::class);
        /** @var User $user */
        $user = $user_repository->find($user_id);
        if ($user === null) {
            return false;
        }
        return $this->deleteUser($user);
    }
}
