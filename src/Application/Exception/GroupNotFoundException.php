<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Exception;

class GroupNotFoundException extends Exception
{
    public function __construct(int $group_id)
    {
        parent::__construct('Group with ID ' . $group_id . ' not found.');
    }
}
