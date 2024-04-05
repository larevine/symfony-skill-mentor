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
}