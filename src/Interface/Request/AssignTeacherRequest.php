<?php

namespace App\Interface\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AssignTeacherRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public readonly int $teacher_id,
    ) {
    }
}
