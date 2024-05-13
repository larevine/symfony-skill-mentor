<?php

declare(strict_types=1);

namespace App\DataFixtures\Dev;

use App\DTO\ManageUserDTO;
use App\Entity\Enum\Default\Roles;
use App\Entity\Enum\Default\Skills;
use App\Entity\Enum\SkillLevel;
use App\Entity\Enum\UserStatus;
use App\Manager\UserManager;
use App\Service\Builder\UserBuilderService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserBuilderService $user_builder,
        private readonly UserManager $user_manager,
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
                'roles' => [Roles::BASE->value, Roles::ADMIN->value],
                'skill_ids' => []
            ],
            [
                'reference' => 'USER_TEACHER_1_REFERENCE',
                'email' => 'teacher@localhost.com',
                'password' => 'admin',
                'name' => 'Учитель',
                'surname' => 'Учителев',
                'roles' => [Roles::BASE->value, Roles::TEACHER->value],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::EXPERT->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_1_REFERENCE',
                'email' => 'student-1@localhost.com',
                'password' => 'admin',
                'name' => 'Студент',
                'surname' => 'Студентов',
                'roles' => [Roles::BASE->value, Roles::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_2_REFERENCE',
                'email' => 'student-2@localhost.com',
                'password' => 'admin',
                'name' => 'Алдуин',
                'surname' => 'Лотар',
                'roles' => [Roles::BASE->value, Roles::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_3_REFERENCE',
                'email' => 'student-3@localhost.com',
                'password' => 'admin',
                'name' => 'Виктор',
                'surname' => 'Нефарий',
                'roles' => [Roles::BASE->value, Roles::STUDENT->value],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
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
                    status: UserStatus::ACTIVE,
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
