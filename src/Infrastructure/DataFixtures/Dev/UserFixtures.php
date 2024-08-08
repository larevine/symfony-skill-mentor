<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Dev;

use App\Application\DTO\ManageUserDTO;
use App\Application\Service\Management\UserManagementService;
use App\Application\Service\UserService;
use App\Domain\ValueObject\RolesEnum;
use App\Domain\ValueObject\SkillLevelEnum;
use App\Domain\ValueObject\SkillsEnum;
use App\Domain\ValueObject\UserStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserManagementService $user_builder,
        private readonly UserService $user_manager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'reference' => 'USER_ADMIN_REFERENCE',
                'email' => 'admin@localhost.com',
                'password' => 'admin',
                'name' => 'Админ',
                'surname' => 'Админов',
                'roles' => [RolesEnum::BASE->value, RolesEnum::ADMIN->value],
                'skill_ids' => []
            ],
            [
                'reference' => 'USER_TEACHER_1_REFERENCE',
                'email' => 'teacher@localhost.com',
                'password' => 'admin',
                'name' => 'Учитель',
                'surname' => 'Учителев',
                'roles' => [RolesEnum::BASE->value, RolesEnum::TEACHER->value],
                'skill_ids' => [
                    $this->getReference(SkillsEnum::SYMFONY->name . '_' . SkillLevelEnum::EXPERT->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_1_REFERENCE',
                'email' => 'student-1@localhost.com',
                'password' => 'admin',
                'name' => 'Студент',
                'surname' => 'Студентов',
                'roles' => [RolesEnum::BASE->value, RolesEnum::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(SkillsEnum::SYMFONY->name . '_' . SkillLevelEnum::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_2_REFERENCE',
                'email' => 'student-2@localhost.com',
                'password' => 'admin',
                'name' => 'Алдуин',
                'surname' => 'Лотар',
                'roles' => [RolesEnum::BASE->value, RolesEnum::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(SkillsEnum::SYMFONY->name . '_' . SkillLevelEnum::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_3_REFERENCE',
                'email' => 'student-3@localhost.com',
                'password' => 'admin',
                'name' => 'Виктор',
                'surname' => 'Нефарий',
                'roles' => [RolesEnum::BASE->value, RolesEnum::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(SkillsEnum::SYMFONY->name . '_' . SkillLevelEnum::BASIC->toString())->getId(),
                ],
            ]
        ];

        foreach ($users as $user) {
            $user_id = $this->user_builder->saveUserWithRelatedEntities(
                new ManageUserDTO(
                    email: $user['email'],
                    password: $user['password'],
                    name: $user['name'],
                    surname: $user['surname'],
                    status: UserStatusEnum::ACTIVE,
                    roles: $user['roles'],
                    skill_ids: $user['skill_ids'],
                ),
            );

            $this->addReference($user['reference'], $this->user_manager->findUser($user_id));
        }
    }

    public function getOrder(): int
    {
        return 3;
    }
}
