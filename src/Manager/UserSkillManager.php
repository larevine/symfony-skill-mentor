<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Skill;
use App\Entity\User;
use App\Entity\UserSkill;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserSkillManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function addUserSkill(User $user, Skill $skill): void
    {
        $user_skill = new UserSkill();
        $user_skill->setUser($user);
        $user_skill->setSkill($skill);
        $user->addSkill($user_skill);
        $skill->addUser($user_skill);
        $this->em->persist($user_skill);
        $this->em->flush();
    }

    public function addUserSkills(User $user, Skill ...$skills): void
    {
        foreach ($skills as $skill) {
            $this->addUserSkill($user, $skill);
        }
    }

    public function removeUserSkill(User $user, Skill $skill): void
    {
        $user_skill = $this->em->getRepository(UserSkill::class)->findOneBy(['user' => $user, 'skill' => $skill]);
        if ($user_skill) {
            $user->removeSkill($user_skill);
            $skill->removeUser($user_skill);
            $this->em->remove($user_skill);
            $this->em->flush();
        }
    }

    public function removeUserSkills(User $user): void
    {
        $user_skills = $this->em->getRepository(UserSkill::class)->findBy(['user' => $user]);
        foreach ($user_skills as $user_skill) {
            $user->removeSkill($user_skill);
            $skill = $user_skill->getSkill();
            $skill->removeUser($user_skill);
            $this->em->remove($user_skill);
        }
        $this->em->flush();
    }
}
