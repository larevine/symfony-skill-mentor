<?php

declare(strict_types=1);

namespace App\Interface\DTO\Filter;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractUserFilterRequest
{
    #[Assert\Type('array')]
    #[Assert\All([
        'constraints' => [
            new Assert\Type('integer'),
            new Assert\Positive(),
        ],
    ])]
    private ?array $skill_ids = null;

    #[Assert\Type('array')]
    #[Assert\All([
        'constraints' => [
            new Assert\Type('integer'),
            new Assert\Positive(),
        ],
    ])]
    private ?array $group_ids = null;

    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $search = null;

    #[Assert\Type('string')]
    private ?string $sort_by = null;

    #[Assert\Type('string')]
    #[Assert\Choice(['ASC', 'DESC'])]
    private ?string $sort_order = null;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1)]
    private int $page = 1;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    private int $per_page = 20;

    public function __construct(
        ?array $skill_ids = null,
        ?array $group_ids = null,
        ?string $search = null,
        ?string $sort_by = null,
        ?string $sort_order = null,
        int $page = 1,
        int $per_page = 20,
    ) {
        $this->setSkillIds($skill_ids);
        $this->setGroupIds($group_ids);
        $this->setSearch($search);
        $this->setSortBy($sort_by);
        $this->setSortOrder($sort_order);
        $this->setPage($page);
        $this->setPerPage($per_page);
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

    public function getSortBy(): ?string
    {
        return $this->sort_by;
    }

    public function setSortBy(?string $sort_by): void
    {
        $this->sort_by = $sort_by;
    }

    public function getSortOrder(): ?string
    {
        return $this->sort_order;
    }

    public function setSortOrder(?string $sort_order): void
    {
        $this->sort_order = $sort_order;
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

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->per_page;
    }

    public function getLimit(): int
    {
        return $this->per_page;
    }
}
