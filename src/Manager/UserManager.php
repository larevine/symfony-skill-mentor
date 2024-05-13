<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ManageUserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $password_hasher,
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
        if (!is_null($manage_user_DTO->password)) {
            $user->setPassword($this->password_hasher->hashPassword($user, $manage_user_DTO->password));
        }
        $token = base64_encode(random_bytes(20));
        $user->setToken($token);
        $user->setName($manage_user_DTO->name);
        $user->setSurname($manage_user_DTO->surname);
        $user->setStatus($manage_user_DTO->status);
        $user->setUpdatedAt();
        $user->setRoles($manage_user_DTO->roles);
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

    public function updateUserToken(string $email): false|string
    {
        $user = $this->findUserByEmail($email);
        if ($user === null) {
            return false;
        }
        $token = base64_encode(random_bytes(20));
        $user->setToken($token);
        $this->em->flush();

        return $token;
    }
}
