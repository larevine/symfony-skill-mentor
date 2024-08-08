<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\User;
use App\Domain\Entity\UserSkill;
use App\Domain\Repository\IUserSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserSkillRepository extends EntityRepository implements IUserSkillRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(UserSkill::class));
    }

    public function removeByUser(User $user): void
    {
        $user_skills = $this->em->getRepository(UserSkill::class)
            ->findBy(['user' => $user]);
        foreach ($user_skills as $user_skill) {
            $this->em->remove($user_skill);
        }
        $this->em->flush();
    }

    public function save(UserSkill $user_skill): void
    {
        $this->em->persist($user_skill);
        $this->em->flush();
    }

    public function delete(UserSkill $user_skill): void
    {
        $this->em->remove($user_skill);
        $this->em->flush();
    }
}
