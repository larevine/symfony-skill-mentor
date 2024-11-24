<?php

declare(strict_types=1);

namespace App\Interface\DTO;

/**
 * @template T
 */
readonly class ListResponse
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page = 1,
        public int $per_page = 20,
        public int $total_pages = 1,
    ) {
    }

    /**
     * @template U
     * @param array<U> $items
     * @param int $total
     * @param int $page
     * @param int $per_page
     * @return ListResponse<U>
     */
    public static function create(
        array $items,
        int $total,
        int $page = 1,
        int $per_page = 20,
    ): self {
        return new self(
            items: $items,
            total: $total,
            page: $page,
            per_page: $per_page,
            total_pages: (int) ceil($total / $per_page),
        );
    }
}
