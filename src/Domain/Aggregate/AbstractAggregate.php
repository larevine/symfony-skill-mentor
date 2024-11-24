<?php

declare(strict_types=1);

namespace App\Domain\Aggregate;

use App\Domain\ValueObject\EntityId;

abstract class AbstractAggregate
{
    protected EntityId $id;

    public function __construct(EntityId $id)
    {
        $this->id = $id;
    }

    public function getId(): EntityId
    {
        return $this->id;
    }
}
