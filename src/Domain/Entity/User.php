<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['teacher' => Teacher::class, 'student' => Student::class])]
#[ORM\Index(name: 'users__name__idx', columns: ['first_name', 'last_name'])]
#[ORM\Index(name: 'users__type__idx', columns: ['type'])]
#[ORM\HasLifecycleCallbacks]
// API-platform
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC'])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $first_name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $last_name;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $email;

    #[ORM\Column(type: Types::JSON)]
    protected array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $password;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        array $roles = []
    ) {
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->email = $email;
        $this->roles = $roles;
        $this->password = $password;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function updateName(PersonName $name): void
    {
        $this->first_name = $name->getFirstName();
        $this->last_name = $name->getLastName();
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email->getValue();
    }
}
