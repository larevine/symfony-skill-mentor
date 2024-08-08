<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\UserStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ManageUserDTO
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank]
        #[Assert\Length(max: 150)]
        public string $email = '',
        #[Assert\Length(max: 120)]
        public ?string $password = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $surname = '',
        #[Assert\Choice(callback: [UserStatusEnum::class, 'cases'], strict: true)]
        public UserStatusEnum $status = UserStatusEnum::ACTIVE,
        #[Assert\Valid]
        #[Type('array<string>')]
        public array $roles = [],
        #[Assert\Valid]
        #[Type('array<int>')]
        public array $skill_ids = [],
    ) {
    }
}
