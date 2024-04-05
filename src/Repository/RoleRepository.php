<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{
    public function findByName(string $name): ?Role
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('r')
            ->from($this->getClassName(), 'r')
            ->where('r.name = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult();
    }
}