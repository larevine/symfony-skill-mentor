<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    public function findByNameAndLevel(string $name, int $level): ?Skill
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('s')
            ->from($this->getClassName(), 's')
            ->where('s.name = :name')
            ->andWhere('s.level = :level')
            ->setParameter('name', $name)
            ->setParameter('level', $level);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByName(string $name): ?Skill
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s')
            ->where('s.name = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByLevel(int $level): ?Skill
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s')
            ->where('s.level = :level')
            ->setParameter('level', $level);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getSkills(int $page, int $per_page)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s')
            ->setFirstResult($per_page * ($page - 1))
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }
}