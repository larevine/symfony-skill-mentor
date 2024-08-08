<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\User;
use App\Domain\Repository\IUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements IUserRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(User::class));
    }

    public function findByEmail(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email, 'status' => 1]);
    }

    public function findByToken(string $token): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['token' => $token]);
    }

    /**
     * @return User[]
     */
    public function findPaginated(int $page, int $per_page): array
    {
        $qb = $this->em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $per_page)
            ->setMaxResults($per_page);

        return $qb->getQuery()->getResult();
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
