<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

class TeacherNotFoundException extends RuntimeException
{
    public function __construct(int $teacher_id)
    {
        parent::__construct(sprintf('Teacher with id %d not found', $teacher_id));
    }
}
