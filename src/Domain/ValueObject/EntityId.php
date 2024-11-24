<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEntityIdException;

readonly class EntityId
{
    public function __construct(
        private int $id
    ) {
        if ($id === 0) {
            throw InvalidEntityIdException::zero();
        }

        if ($id < 0) {
            throw InvalidEntityIdException::negative($id);
        }
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
