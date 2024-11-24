<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidPersonNameException;

class PersonName
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 255;
    private const PATTERN = '/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u';

    public function __construct(
        private string $first_name,
        private string $last_name,
    ) {
        $first_name = trim($first_name);
        $last_name = trim($last_name);

        if (empty($first_name)) {
            throw InvalidPersonNameException::empty('first name');
        }
        if (empty($last_name)) {
            throw InvalidPersonNameException::empty('last name');
        }

        if (strlen($first_name) < self::MIN_LENGTH) {
            throw InvalidPersonNameException::tooShort('first name', self::MIN_LENGTH);
        }
        if (strlen($last_name) < self::MIN_LENGTH) {
            throw InvalidPersonNameException::tooShort('last name', self::MIN_LENGTH);
        }

        if (strlen($first_name) > self::MAX_LENGTH) {
            throw InvalidPersonNameException::tooLong('first name', self::MAX_LENGTH);
        }
        if (strlen($last_name) > self::MAX_LENGTH) {
            throw InvalidPersonNameException::tooLong('last name', self::MAX_LENGTH);
        }

        if (!preg_match(self::PATTERN, $first_name)) {
            throw InvalidPersonNameException::invalidFormat('first name');
        }
        if (!preg_match(self::PATTERN, $last_name)) {
            throw InvalidPersonNameException::invalidFormat('last name');
        }

        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    public function equals(self $other): bool
    {
        return $this->first_name === $other->first_name
            && $this->last_name === $other->last_name;
    }
}
