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

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserBuilderService $user_builder,
        private readonly UserManager $user_manager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'reference' => 'USER_ADMIN_REFERENCE',
                'email' => 'admin@localhost.com',
                'name' => 'Админ',
                'surname' => 'Админов',
                'roles' => [$this->getReference(Roles::ADMIN->name)->getName()],
                'skill_ids' => []
            ],
            [
                'reference' => 'USER_TEACHER_1_REFERENCE',
                'email' => 'teacher@localhost.com',
                'name' => 'Учитель',
                'surname' => 'Учителев',
                'roles' => [$this->getReference(Roles::TEACHER->name)->getName()],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::EXPERT->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_1_REFERENCE',
                'email' => 'student-1@localhost.com',
                'name' => 'Студент',
                'surname' => 'Студентов',
                'roles' => [$this->getReference(Roles::STUDENT->name)->getName()],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_2_REFERENCE',
                'email' => 'student-2@localhost.com',
                'name' => 'Алдуин',
                'surname' => 'Лотар',
                'roles' => [$this->getReference(Roles::STUDENT->name)->getName()],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
                ],
            ],
            [
                'reference' => 'USER_STUDENT_3_REFERENCE',
                'email' => 'student-3@localhost.com',
                'name' => 'Виктор',
                'surname' => 'Нефарий',
                'roles' => [$this->getReference(Roles::STUDENT->name)->getName()],
                'skill_ids' => [
                    $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::BASIC->toString())->getId(),
                ],
            ]
        ];

        foreach ($users as $user) {
            $user_id = $this->user_builder->saveUserWithRelatedEntities(
                new ManageUserDTO(
                    email: $user['email'],
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
