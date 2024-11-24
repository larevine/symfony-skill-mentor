<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidGroupNameException;

class GroupName
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 255;
    private const PATTERN = '/^[a-zA-Z0-9\s\-]+$/';

    public function __construct(
        private string $name
    ) {
        $name = trim($name);

        if (empty($name)) {
            throw InvalidGroupNameException::empty();
        }

        if (strlen($name) < self::MIN_LENGTH) {
            throw InvalidGroupNameException::tooShort(self::MIN_LENGTH);
        }

        if (strlen($name) > self::MAX_LENGTH) {
            throw InvalidGroupNameException::tooLong(self::MAX_LENGTH);
        }

        if (!preg_match(self::PATTERN, $name)) {
            throw InvalidGroupNameException::invalidFormat();
        }

        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function containsText(string $text): bool
    {
        return str_contains(strtolower($this->name), strtolower($text));
    }
}
