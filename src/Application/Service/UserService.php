<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\ManageUserDTO;
use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\IUserService;
use App\Domain\Entity\User;
use App\Domain\Repository\IUserRepository;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService implements IUserService
{
    public function __construct(
        private IUserRepository $user_repository,
        private UserPasswordHasherInterface $password_hasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function findUser(int $id): ?User
    {
        return $this->user_repository->find($id);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->user_repository->findByEmail($email);
    }

    public function findUserByToken(string $token): ?User
    {
        return $this->user_repository->findByToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers(int $page, int $per_page): array
    {
        return $this->user_repository->findPaginated($page, $per_page);
    }

    public function saveUser(User $user): void
    {
        $this->user_repository->save($user);
    }

    /**
     * {@inheritdoc}
     */
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

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new Exception('Validation failed');
        }

        $this->user_repository->save($user);

        return $user->getId();
    }

    public function deleteUser(User $user): bool
    {
        $this->user_repository->delete($user);
        return true;
    }

    public function deleteUserById(int $user_id): bool
    {
        $user = $this->findUser($user_id);
        if ($user === null) {
            return false;
        }
        return $this->deleteUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserToken(string $email): ?string
    {
        $user = $this->findUserByEmail($email);
        if ($user === null) {
            return null;
        }
        $token = base64_encode(random_bytes(20));
        $user->setToken($token);
        $this->user_repository->save($user);

        return $token;
    }

    /**
     * @throws ValidationException
     */
    private function validateUser(User $user): void
    {
        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
