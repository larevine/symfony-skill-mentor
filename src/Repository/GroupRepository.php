<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Grade;
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

    public function findByNameAndGrade(string $name, Grade $grade): ?Group
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('g')
            ->from($this->getClassName(), 'g')
            ->leftJoin(Grade::class, 'gr', Join::WITH, 'gr.id = g.grade_id')
            ->where('g.name = :name')
            ->andWhere('gr.id = :grade_id')
            ->setParameter('name', $name)
            ->setParameter('grade_id', $grade->getId());

        return $qb->getQuery()->getOneOrNullResult();
    }
}