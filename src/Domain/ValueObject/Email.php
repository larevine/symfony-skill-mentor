<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEmailException;

readonly class Email
{
    private const MAX_LENGTH = 255;
    private string $email;

    public function __construct(string $email)
    {
        $email = trim($email);
        if (empty($email)) {
            throw InvalidEmailException::empty();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalid($email);
        }

        if (strlen($email) > self::MAX_LENGTH) {
            throw InvalidEmailException::tooLong($email, self::MAX_LENGTH);
        }

        $this->email = $email;
    }

    public function getValue(): string
    {
        return $this->email;
    }

    public function equals(self $other): bool
    {
        return $this->email === $other->email;
    }

    public function getDomain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }

    public function getLocalPart(): string
    {
        return substr($this->email, 0, strpos($this->email, '@'));
    }
}
