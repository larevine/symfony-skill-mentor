<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(int $user_id)
    {
        parent::__construct('User with ID ' . $user_id . ' not found.');
    }
}
