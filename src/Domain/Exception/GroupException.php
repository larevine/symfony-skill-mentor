<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

class GroupException extends DomainException
{
    public static function fromDomainException(DomainException $e): self
    {
        return new self($e->getMessage(), 0, $e);
    }
}
