<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Skill;
use App\Domain\Repository\ISkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository implements ISkillRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Skill::class));
    }

    /**
     * @return Skill[]
     */
    public function findPaginated(int $page, int $per_page): array
    {
        $qb = $this->em->getRepository(Skill::class)
            ->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $per_page)
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }

    public function findByName(string $name): ?Skill
    {
        return $this->em->getRepository(Skill::class)
            ->findOneBy(['name' => $name]);
    }

    public function save(Skill $skill): void
    {
        $this->em->persist($skill);
        $this->em->flush();
    }

    public function delete(Skill $skill): void
    {
        $this->em->remove($skill);
        $this->em->flush();
    }
}
