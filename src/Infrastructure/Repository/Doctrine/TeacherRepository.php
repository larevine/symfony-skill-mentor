<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Teacher;
use App\Domain\Repository\TeacherRepositoryInterface;
use App\Interface\DTO\TeacherFilterRequest;

class TeacherRepository extends AbstractBaseRepository implements TeacherRepositoryInterface
{
    public function findById(int $id): ?Teacher
    {
        return $this->find($id);
    }

    public function findByFilter(TeacherFilterRequest $filter): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($filter->search !== null) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.first_name', ':search'),
                    $qb->expr()->like('t.last_name', ':search'),
                    $qb->expr()->like('t.email', ':search')
                )
            )
            ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->skill_ids) {
            $qb->join('t.skills', 'sp')
                ->join('sp.skill', 's')
                ->andWhere('s.id IN (:skill_sds)')
                ->setParameter('skill_sds', $filter->skill_ids);
        }

        if ($filter->sort_by) {
            foreach ($filter->sort_by as $field) {
                $qb->addOrderBy("t.$field", $filter->sort_order);
            }
        }

        $qb->setFirstResult(($filter->page - 1) * $filter->per_page)
            ->setMaxResults($filter->per_page);

        return $qb->getQuery()->getResult();
    }

    public function countByFilter(TeacherFilterRequest $filter): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if ($filter->search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.first_name', ':search'),
                    $qb->expr()->like('t.last_name', ':search'),
                    $qb->expr()->like('t.email', ':search')
                )
            )
            ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->skill_ids) {
            $qb->join('t.skills', 'sp')
                ->join('sp.skill', 's')
                ->andWhere('s.id IN (:skill_ids)')
                ->setParameter('skill_ids', $filter->skill_ids);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<Teacher>
     */
    public function findAll(): array
    {
        return parent::findAll();
    }
}
