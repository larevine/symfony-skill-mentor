<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Interface\DTO\StudentFilterRequest;
use Doctrine\ORM\QueryBuilder;

class StudentRepository extends AbstractBaseRepository implements StudentRepositoryInterface
{
    public function findById(int $id): ?Student
    {
        return $this->find($id);
    }

    /**
     * @return array<Student>
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findByDesiredSkill(int $skill_id): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.desired_skills', 'ds')
            ->where('ds.id = :skill_id')
            ->setParameter('skill_id', $skill_id);

        return $qb->getQuery()->getResult();
    }

    public function findWithoutGroup(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.groups', 'g')
            ->where('g.id IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function findEligibleForGroup(Group $group): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.groups', 'g')
            ->leftJoin('s.skills', 'sp')
            ->leftJoin('sp.skill', 'sk')
            ->leftJoin('g.required_skills', 'grs')
            ->where('g.id IS NULL')
            ->andWhere('sk.id IN (:required_skills)')
            ->setParameter('required_skills', $group->getRequiredSkills()->map(fn ($s) => $s->getId())->toArray())
            ->groupBy('s.id')
            ->having('COUNT(DISTINCT sk.id) >= :required_skill_count')
            ->setParameter('required_skill_count', $group->getRequiredSkills()->count());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<Student>
     */
    public function findByFilter(StudentFilterRequest $filter): array
    {
        $qb = $this->createFilterQueryBuilder($filter);

        if ($filter->getLimit() > 0) {
            $qb->setMaxResults($filter->getLimit());
            $qb->setFirstResult($filter->getOffset());
        }

        return $qb->getQuery()->getResult();
    }

    public function countByFilter(StudentFilterRequest $filter): int
    {
        $qb = $this->createFilterQueryBuilder($filter)
            ->select('COUNT(DISTINCT s.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function createFilterQueryBuilder(StudentFilterRequest $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.skills', 'sp')
            ->leftJoin('sp.skill', 'skill');

        if ($filter->getSkillIds() !== null && count($filter->getSkillIds()) > 0) {
            $qb->andWhere('skill.id IN (:skillIds)')
                ->setParameter('skillIds', $filter->getSkillIds());
        }

        if ($filter->getMinSkillLevel() !== null) {
            $qb->andWhere('sp.level >= :minLevel')
                ->setParameter('minLevel', $filter->getMinSkillLevel());
        }

        if ($filter->getMaxSkillLevel() !== null) {
            $qb->andWhere('sp.level <= :maxLevel')
                ->setParameter('maxLevel', $filter->getMaxSkillLevel());
        }

        if ($filter->getSearchTerm() !== null) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('s.first_name', ':searchTerm'),
                    $qb->expr()->like('s.last_name', ':searchTerm'),
                    $qb->expr()->like('s.email', ':searchTerm')
                )
            )
                ->setParameter('searchTerm', '%' . $filter->getSearchTerm() . '%');
        }

        return $qb;
    }

    private function buildFilterCriteria(StudentFilterRequest $filter, QueryBuilder $qb): void
    {
        if ($filter->search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('s.first_name', ':search'),
                    $qb->expr()->like('s.last_name', ':search'),
                    $qb->expr()->like('s.email', ':search')
                )
            )
            ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->skill_ids) {
            $qb->andWhere('sp.skill IN (:skill_ids)')
                ->setParameter('skill_ids', $filter->skill_ids);
        }

        if ($filter->group_ids) {
            $qb->andWhere('g.id IN (:group_ids)')
                ->setParameter('group_ids', $filter->group_ids);
        }
    }

    private function getLimit(StudentFilterRequest $filter): int
    {
        return $filter->per_page;
    }

    private function getOffset(StudentFilterRequest $filter): int
    {
        return ($filter->page - 1) * $filter->per_page;
    }

    private function getSearchTerm(StudentFilterRequest $filter): ?string
    {
        return $filter->search;
    }

    private function getMinSkillLevel(StudentFilterRequest $filter): ?int
    {
        return $filter->min_skill_level ?? null;
    }

    private function getMaxSkillLevel(StudentFilterRequest $filter): ?int
    {
        return $filter->max_skill_level ?? null;
    }
}
