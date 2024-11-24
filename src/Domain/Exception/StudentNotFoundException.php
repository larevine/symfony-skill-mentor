<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

class StudentNotFoundException extends RuntimeException
{
    public function __construct(int $student_id)
    {
        parent::__construct(sprintf('Student with id %d not found', $student_id));
    }
}
