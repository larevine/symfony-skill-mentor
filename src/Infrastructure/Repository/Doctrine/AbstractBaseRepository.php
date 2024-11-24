<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractBaseRepository extends EntityRepository
{
    public function __construct(protected EntityManagerInterface $em, string $entity_class)
    {
        parent::__construct($em, $em->getClassMetadata($entity_class));
    }

    public function findById(int $id): ?object
    {
        return $this->find($id);
    }

    public function save(object $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function remove(object $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }
}
