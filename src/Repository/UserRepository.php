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
            ->from(User::class, 'u')
            ->where('u.id = :id')
            ->andWhere('u.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', UserStatus::ACTIVE);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findActiveUsersByEmail(string $email): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('u')
            ->from(User::class, 'u', 'u')
            ->where('u.email = :email')
            ->andWhere('u.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', UserStatus::ACTIVE);

        return $qb->getQuery()->getResult();
    }

    public function findUsersBySurname(string $surname): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.surname = :surname')
            ->setParameter('surname', $surname);

        return $qb->getQuery()->getResult();
    }

    public function getUsers(int $page, int $perPage): array
    {
        $page = $page > 0 ? $page : 1;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        return $qb->getQuery()->getResult();
    }
}