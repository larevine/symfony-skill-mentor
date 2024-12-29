<?php

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class AddStudentRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public readonly int $student_id,
    ) {
    }
}
