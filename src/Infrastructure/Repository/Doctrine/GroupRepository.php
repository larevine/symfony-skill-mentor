<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Group;
use App\Domain\Repository\IGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository implements IGroupRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Group::class));
    }

    public function findByName(string $name): ?Group
    {
        return $this->em->getRepository(Group::class)->findOneBy(['name' => $name]);
    }

    /**
     * @return Group[]
     */
    public function findPaginated(int $page, int $per_page): array
    {
        $qb = $this->em->getRepository(Group::class)
            ->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->setFirstResult(($page - 1) * $per_page)
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }

    public function save(Group $group): void
    {
        $this->em->persist($group);
        $this->em->flush();
    }

    public function delete(Group $group): void
    {
        $this->em->remove($group);
        $this->em->flush();
    }
}
