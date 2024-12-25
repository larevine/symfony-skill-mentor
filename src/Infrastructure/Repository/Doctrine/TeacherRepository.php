<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Teacher;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Interface\DTO\Filter\TeacherFilterRequest;
use Doctrine\ORM\QueryBuilder;

class TeacherRepository extends AbstractBaseRepository implements TeacherRepositoryInterface
{
    public function findById(int $id): ?Teacher
    {
        return $this->find($id);
    }

    public function findOneByEmail(string $email): ?Teacher
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByFilter(TeacherFilterRequest $filter): array
    {
        $qb = $this->createFilterQueryBuilder($filter);

        if ($filter->getLimit() > 0) {
            $qb->setMaxResults($filter->getLimit());
            $qb->setFirstResult($filter->getOffset());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<Teacher>
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    private function createFilterQueryBuilder(TeacherFilterRequest $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t');

        if ($filter->getSkillIds()) {
            $qb->leftJoin('t.skill_proficiencies', 'sp')
                ->leftJoin('sp.skill', 'skill');
        }

        if ($filter->getGroupIds()) {
            $qb->leftJoin('t.groups', 'g');
        }

        $this->applyFilterConditions($qb, $filter);

        // Apply sorting
        if ($filter->getSortBy()) {
            $sort_field = match ($filter->getSortBy()) {
                'first_name', 'last_name', 'email' => 't.' . $filter->getSortBy(),
                default => 't.id'
            };
            $qb->addOrderBy($sort_field, $filter->getSortOrder() ?? 'ASC');
        }

        return $qb;
    }

    private function applyFilterConditions(QueryBuilder $qb, TeacherFilterRequest $filter): void
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
                    $qb->expr()->like('t.first_name', ':search'),
                    $qb->expr()->like('t.last_name', ':search'),
                    $qb->expr()->like('t.email', ':search')
                )
            )
                ->setParameter('search', '%' . $filter->getSearch() . '%');
        }
    }
}
