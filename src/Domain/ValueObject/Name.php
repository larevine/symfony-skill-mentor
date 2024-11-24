<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObjectException;

class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (mb_strlen($value) < 2 || mb_strlen($value) > 255) {
            throw ValueObjectException::invalidName($value);
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
