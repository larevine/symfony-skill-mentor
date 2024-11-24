<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;

readonly class CreateTeacherCommand
{
    private Email $email;
    private PersonName $person_name;

    public function __construct(
        string $email,
        private string $password,
        string $name,
        string $surname,
        private int $max_groups = 1,
    ) {
        $this->email = new Email($email);
        $this->person_name = new PersonName($name, $surname);
    }

    public function getEmail(): string
    {
        return $this->email->getValue();
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->person_name->getFirstName();
    }

    public function getSurname(): string
    {
        return $this->person_name->getLastName();
    }

    public function getMaxGroups(): int
    {
        return $this->max_groups;
    }

    public function getEmailObject(): Email
    {
        return $this->email;
    }

    public function getPersonName(): PersonName
    {
        return $this->person_name;
    }
}
