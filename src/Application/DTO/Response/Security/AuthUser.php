<?php

declare(strict_types=1);

namespace App\Application\DTO\Response\Security;

use App\Domain\ValueObject\Roles;
use App\Domain\ValueObject\UserStatus;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthUser implements UserInterface
{
    private string $identifier;
    /** @var array<string> */
    private array $roles;
    private UserStatus $status;

    private function __construct(string $identifier, array $roles, UserStatus $status)
    {
        $this->identifier = $identifier;
        $this->roles = $roles;
        $this->status = $status;
    }

    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['username'])) {
            throw new InvalidArgumentException('Username is required in payload');
        }

        return new self(
            identifier: $payload['username'],
            roles: $payload['roles'] ?? [Roles::BASE->value],
            status: UserStatus::from($payload['status'] ?? UserStatus::ACTIVE->value)
        );
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase as we don't store sensitive data
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }
}
