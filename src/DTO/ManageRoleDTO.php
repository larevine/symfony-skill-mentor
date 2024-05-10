<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ManageRoleDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',
    ) {
    }

    public static function fromEntity(Role $role): self
    {
        return new self(...[
            'name' => $role->getName()
        ]);
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->request->get('name') ?? $request->query->get('name'),
        );
    }
}
