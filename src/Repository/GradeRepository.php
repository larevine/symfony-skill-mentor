<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\ORM\EntityRepository;

class GradeRepository extends EntityRepository
{
    public function findByName(string $name): ?Grade
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('g')
            ->from($this->getClassName(), 'g')
            ->select('g')
            ->where('g.name = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getGrades(int $page, int $per_page)
    {
        $page = $page > 0 ? $page : 1;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('g')
            ->from($this->getClassName(), 'g')
            ->orderBy('g.id', 'DESC')
            ->setFirstResult(($per_page * ($page - 1)))
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }
}