<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;

readonly class UpdateTeacherCommand
{
    private Email $email;
    private ?PersonName $person_name;

    public function __construct(
        string $email,
        ?string $name = null,
        ?string $surname = null,
        private ?string $password = null,
        private ?int $max_groups = null,
    ) {
        $this->email = new Email($email);
        $this->person_name = $name !== null && $surname !== null
            ? new PersonName($name, $surname)
            : null;
    }

    public function getEmail(): string
    {
        return $this->email->getValue();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getName(): ?string
    {
        return $this->person_name?->getFirstName();
    }

    public function getSurname(): ?string
    {
        return $this->person_name?->getLastName();
    }

    public function getMaxGroups(): ?int
    {
        return $this->max_groups;
    }

    public function getEmailObject(): Email
    {
        return $this->email;
    }

    public function getPersonName(): ?PersonName
    {
        return $this->person_name;
    }
}
