<?php

declare(strict_types=1);

namespace App\DataFixtures\Dev;

use App\DTO\ManageGroupDTO;
use App\Entity\Enum\Default\Skills;
use App\Entity\Enum\SkillLevel;
use App\Manager\GroupManager;
use App\Service\Builder\GroupBuilderService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly GroupBuilderService $group_builder,
        private readonly GroupManager        $group_manager,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $groups = [
            [
                'reference' => 'GROUP_SYMFONY_ADVANCED_REFERENCE',
                'name' => 'Symfony Advanced Level',
                'limit_teachers' => 2,
                'limit_students' => 10,
                'skill_id' => $this->getReference(Skills::SYMFONY->name . '_' . SkillLevel::ADVANCED->toString())->getId(),
                'level' => SkillLevel::ADVANCED->toString(),
                'user_ids' => [
                    $this->getReference('USER_TEACHER_1_REFERENCE')->getId(),
                    $this->getReference('USER_STUDENT_1_REFERENCE')->getId(),
                    $this->getReference('USER_STUDENT_2_REFERENCE')->getId(),
                ],
            ],
            [
                'reference' => 'GROUP_PHP_BASIC_REFERENCE',
                'name' => 'PHP Basic Level',
                'limit_teachers' => 1,
                'limit_students' => 3,
                'skill_id' => $this->getReference(Skills::PHP->name . '_' . SkillLevel::BASIC->toString())->getId(),
                'level' => SkillLevel::BASIC->toString(),
                'user_ids' => [
                    $this->getReference('USER_TEACHER_1_REFERENCE')->getId(),
                    $this->getReference('USER_STUDENT_1_REFERENCE')->getId(),
                    $this->getReference('USER_STUDENT_2_REFERENCE')->getId(),
                ],
            ],
        ];

        foreach ($groups as $group) {
            $group_id = $this->group_builder->saveGroupWithRelatedEntities(
                new ManageGroupDTO(
                    name: $group['name'],
                    limit_teachers: $group['limit_teachers'],
                    limit_students: $group['limit_students'],
                    skill_id: $group['skill_id'],
                    level: $group['level'],
                    user_ids: $group['user_ids'],
                ),
            );

            $this->addReference($group['reference'], $this->group_manager->findGroup($group_id));
        }
    }

    public function getOrder(): int
    {
        return 4;
    }
}
