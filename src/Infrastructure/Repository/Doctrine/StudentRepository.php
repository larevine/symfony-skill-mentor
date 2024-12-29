<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Repository\StudentRepositoryInterface;
use App\Interface\DTO\Filter\StudentFilterRequest;
use Doctrine\ORM\QueryBuilder;

class StudentRepository extends AbstractBaseRepository implements StudentRepositoryInterface
{
    public function findById(int $id): ?Student
    {
        return $this->find($id);
    }

    public function findOneByEmail(string $email): ?Student
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return array<Student>
     */
    public function findAll(): array
    {
        return parent::findAll();
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
            ->leftJoin('s.skill_proficiencies', 'sp')
            ->leftJoin('sp.skill', 'skill')
            ->where('g.id IS NULL')
            ->andWhere('skill.id IN (:required_skills)')
            ->setParameter('required_skills', $group->getRequiredSkills()->map(fn ($s) => $s->getId())->toArray())
            ->groupBy('s.id')
            ->having('COUNT(DISTINCT skill.id) >= :required_skill_count')
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

    private function createFilterQueryBuilder(StudentFilterRequest $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s');

        if ($filter->getSkillIds()) {
            $qb->leftJoin('s.skill_proficiencies', 'sp')
                ->leftJoin('sp.skill', 'skill');
        }

        if ($filter->getGroupIds()) {
            $qb->leftJoin('s.groups', 'g');
        }

        $this->applyFilterConditions($qb, $filter);

        // Apply sorting
        if ($filter->getSortBy()) {
            $sort_field = match ($filter->getSortBy()) {
                'first_name', 'last_name', 'email' => 's.' . $filter->getSortBy(),
                default => 's.id'
            };
            $qb->addOrderBy($sort_field, $filter->getSortOrder() ?? 'ASC');
        }

        return $qb;
    }

    private function applyFilterConditions(QueryBuilder $qb, StudentFilterRequest $filter): void
    {
        if ($filter->getSkillIds()) {
            $qb->andWhere('skill.id IN (:skill_ids)')
                ->setParameter('skill_ids', $filter->getSkillIds());
        }

        if ($filter->getGroupIds()) {
            $qb->andWhere('g.id IN (:group_ids)')
                ->setParameter('group_ids', $filter->getGroupIds());
        }

        if ($filter->getSearch()) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('s.first_name', ':search'),
                    $qb->expr()->like('s.last_name', ':search'),
                    $qb->expr()->like('s.email', ':search')
                )
            )
                ->setParameter('search', '%' . $filter->getSearch() . '%');
        }
    }
}
