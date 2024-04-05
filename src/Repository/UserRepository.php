<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\UserStatus;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findActiveUsersById(int $id): ?User
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('u')
            ->from($this->getClassName(), 'u')
            ->where('u.id = :id')
            ->andWhere('u.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', UserStatus::ACTIVE);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findActiveUsersByEmail(string $email): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->select('u')
            ->where('u.email = :email')
            ->andWhere('u.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', UserStatus::ACTIVE);

        return $qb->getQuery()->getResult();
    }

    public function findUsersBySurname(string $surname): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->select('u')
            ->where('u.surname = :surname')
            ->setParameter('surname', $surname);

        return $qb->getQuery()->getResult();
    }
}