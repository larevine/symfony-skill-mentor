<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

class GroupNotFoundException extends RuntimeException
{
    public function __construct(int $group_id)
    {
        parent::__construct(sprintf('Group with id %d not found', $group_id));
    }
}
