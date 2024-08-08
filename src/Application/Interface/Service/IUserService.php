<?php

declare(strict_types=1);

namespace App\Application\Interface\Service;

use App\Application\DTO\ManageUserDTO;
use App\Domain\Entity\User;
use Random\RandomException;

interface IUserService
{
    public function findUser(int $id): ?User;
    public function findUserByEmail(string $email): ?User;
    public function findUserByToken(string $token): ?User;

    /**
     * @return User[]
     */
    public function getUsers(int $page, int $per_page): array;
    public function saveUser(User $user): void;

    /**
     * @throws RandomException
     */
    public function saveUserFromDTO(User $user, ManageUserDTO $manage_user_DTO): ?int;
    public function deleteUser(User $user): bool;
    public function deleteUserById(int $user_id): bool;

    /**
     * @throws RandomException
     */
    public function updateUserToken(string $email): ?string;
}
