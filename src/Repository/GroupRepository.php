<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Skill;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class GroupRepository extends EntityRepository
{
    public function findByName(string $name): ?Group
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

    public function findByNameAndSkill(string $name, Skill $skill): ?Group
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('g')
            ->from($this->getClassName(), 'g')
            ->leftJoin(Skill::class, 's', Join::WITH, 's.id = g.skill_id')
            ->where('g.name = :name')
            ->andWhere('s.id = :skill_id')
            ->setParameter('name', $name)
            ->setParameter('skill_id', $skill->getId());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getGroups(int $page, int $per_page)
    {
        $page = $page > 0 ? $page : 1;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('g')
            ->from($this->getClassName(), 'g')
            ->orderBy('g.id', 'DESC')
            ->setFirstResult(($page - 1) * $per_page)
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }
}
