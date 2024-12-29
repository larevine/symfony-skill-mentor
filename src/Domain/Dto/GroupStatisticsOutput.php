<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    shortName: 'GroupStatistics',
    graphQlOperations: [
        new Query(
            resolver: 'App\Interface\GraphQL\Resolver\GroupStatsResolver',
            read: false,
            name: 'statisticsGroupStatistics'
        )
    ]
)]
final readonly class GroupStatisticsOutput
{
    public function __construct(
        #[SerializedName('group_id')]
        public int $group_id,
        #[SerializedName('group_name')]
        public string $group_name,
        #[SerializedName('student_count')]
        public int $student_count,
        #[SerializedName('min_students')]
        public int $min_students,
        #[SerializedName('max_students')]
        public int $max_students,
        #[SerializedName('teacher_name')]
        public string $teacher_name,
    ) {
    }
}
