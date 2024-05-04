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

    public function getRoles(int $page, int $per_page)
    {
        $page = $page > 0 ? $page : 1;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('r')
            ->from($this->getClassName(), 'r')
            ->setFirstResult($per_page * ($page - 1))
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }
}