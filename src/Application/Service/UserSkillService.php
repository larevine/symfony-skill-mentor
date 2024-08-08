<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Exception\ValidationException;
use App\Application\Interface\Service\IUserSkillService;
use App\Domain\Entity\Skill;
use App\Domain\Entity\User;
use App\Domain\Entity\UserSkill;
use App\Domain\Repository\IUserSkillRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserSkillService implements IUserSkillService
{
    public function __construct(
        private IUserSkillRepository $user_skill_repository,
        private ValidatorInterface $validator,
    ) {
    }

    // TODO  добавить level, удалять текущий каждый раз

    /**
     * {@inheritdoc}
     */
    public function addUserSkill(User $user, Skill $skill, int $level = 1): void
    {
        $user_skill = new UserSkill();
        $user_skill->setUser($user);
        $user_skill->setSkill($skill);
        $user->addSkill($user_skill);
        $skill->addUser($user_skill);
        $this->validateUserSkill($user_skill);
        $this->user_skill_repository->save($user_skill);
    }

    /**
     * {@inheritdoc}
     */
    public function addUserSkills(User $user, Skill ...$skills): void
    {
        foreach ($skills as $skill) {
            $this->addUserSkill($user, $skill);
        }
    }

    public function removeUserSkill(UserSkill $user_skill): void
    {
        $this->user_skill_repository->delete($user_skill);
    }

    public function removeUserSkills(User $user): void
    {
        $this->user_skill_repository->removeByUser($user);
    }

    /**
     * @throws ValidationException
     */
    private function validateUserSkill(UserSkill $userSkill): void
    {
        $violations = $this->validator->validate($userSkill);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
