<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidGroupCapacityException;

readonly class GroupCapacity
{
    private const MIN_CAPACITY = 1;
    private const MAX_CAPACITY = 100;

    public function __construct(
        private int $min_students,
        private int $max_students,
    ) {
        if ($min_students === 0) {
            throw InvalidGroupCapacityException::zero();
        }

        if ($min_students < 0) {
            throw InvalidGroupCapacityException::negative($min_students);
        }

        if ($max_students > self::MAX_CAPACITY) {
            throw InvalidGroupCapacityException::tooLarge($max_students, self::MAX_CAPACITY);
        }

        if ($min_students > $max_students) {
            throw new InvalidGroupCapacityException('Maximum size cannot be less than minimum size');
        }
    }

    public function getMinStudents(): int
    {
        return $this->min_students;
    }

    public function getMaxStudents(): int
    {
        return $this->max_students;
    }

    public function canAcceptMoreStudents(int $current_count): bool
    {
        return $current_count < $this->max_students;
    }

    public function hasMinimumStudents(int $current_count): bool
    {
        return $current_count <= $this->min_students;
    }

    public function equals(self $other): bool
    {
        return $this->min_students === $other->min_students
            && $this->max_students === $other->max_students;
    }
}
