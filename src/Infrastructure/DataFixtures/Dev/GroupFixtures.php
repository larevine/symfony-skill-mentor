<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Dev;

use App\Application\DTO\ManageGroupDTO;
use App\Application\Service\GroupService;
use App\Application\Service\Management\GroupManagementService;
use App\Domain\ValueObject\SkillLevelEnum;
use App\Domain\ValueObject\SkillsEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class GroupFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly GroupManagementService $group_builder,
        private readonly GroupService $group_manager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $groups = [
            [
                'reference' => 'GROUP_SYMFONY_ADVANCED_REFERENCE',
                'name' => 'Symfony Advanced Level',
                'limit_teachers' => 2,
                'limit_students' => 10,
                'skill_id' => $this->getReference(
                    SkillsEnum::SYMFONY->name . '_' . SkillLevelEnum::ADVANCED->toString(),
                )->getId(),
                'level' => SkillLevelEnum::ADVANCED,
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
                'skill_id' => $this->getReference(SkillsEnum::PHP->name . '_' . SkillLevelEnum::BASIC->toString())->getId(),
                'level' => SkillLevelEnum::BASIC,
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
