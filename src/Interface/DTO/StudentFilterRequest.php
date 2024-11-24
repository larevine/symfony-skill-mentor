<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StudentFilterRequest
{
    #[Assert\Type('array')]
    #[Assert\All([
        'constraints' => [
            new Assert\Type('integer'),
            new Assert\Positive(),
        ],
    ])]
    public ?array $skill_ids;

    #[Assert\Type('array')]
    #[Assert\All([
        'constraints' => [
            new Assert\Type('integer'),
            new Assert\Positive(),
        ],
    ])]
    public ?array $group_ids;

    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 255)]
    public ?string $search;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1)]
    public int $page;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    public int $per_page;

    public function __construct(
        ?array $skill_ids = null,
        ?array $group_ids = null,
        ?string $search = null,
        int $page = 1,
        int $per_page = 20,
    ) {
        $this->skill_ids = $skill_ids;
        $this->group_ids = $group_ids;
        $this->search = $search;
        $this->page = $page;
        $this->per_page = $per_page;
    }

    public function getSkillIds(): ?array
    {
        return $this->skill_ids;
    }

    public function setSkillIds(?array $skill_ids): void
    {
        $this->skill_ids = $skill_ids;
    }

    public function getGroupIds(): ?array
    {
        return $this->group_ids;
    }

    public function setGroupIds(?array $group_ids): void
    {
        $this->group_ids = $group_ids;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPerPage(): int
    {
        return $this->per_page;
    }

    public function setPerPage(int $per_page): void
    {
        $this->per_page = $per_page;
    }

    public function getLimit(): int
    {
        return $this->per_page;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->per_page;
    }

    public function getMinSkillLevel(): ?int
    {
        return null; // Implement if needed
    }

    public function getMaxSkillLevel(): ?int
    {
        return null; // Implement if needed
    }

    public function getSearchTerm(): ?string
    {
        return $this->search;
    }
}
