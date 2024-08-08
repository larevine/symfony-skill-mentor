<?php

declare(strict_types=1);

namespace App\Application\Security\Auth;

use Symfony\Component\Security\Core\User\UserInterface;

/*
 * Для JWT аутентификации
 */
class AuthUser implements UserInterface
{
    private string $email;

    /** @var string[] */
    private array $roles;

    public function __construct(array $credentials)
    {
        $this->email = $credentials['username'];
        $this->roles = array_unique(array_merge($credentials['roles'] ?? [], ['BASE_ROLE']));
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}
