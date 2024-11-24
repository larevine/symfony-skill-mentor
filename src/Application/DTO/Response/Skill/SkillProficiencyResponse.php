<?php

declare(strict_types=1);

namespace App\Application\DTO\Response\Skill;

use App\Domain\Entity\SkillProficiency;

readonly class SkillProficiencyResponse
{
    private function __construct(
        public int $skill_id,
        public string $skill_name,
        public int $level,
        public string $level_label,
        public ?string $description,
    ) {
    }

    public static function fromEntity(SkillProficiency $skill_proficiency): self
    {
        $level = $skill_proficiency->getLevel();

        return new self(
            skill_id: $skill_proficiency->getSkill()->getId(),
            skill_name: $skill_proficiency->getSkill()->getName(),
            level: $level->getValue(),
            level_label: $level->getLabel(),
            description: $skill_proficiency->getSkill()->getDescription(),
        );
    }

    /**
     * @return array<self>
     */
    public static function fromEntities(array $skill_proficiencies): array
    {
        return array_map(
            static fn (SkillProficiency $skill_proficiency): self => self::fromEntity($skill_proficiency),
            $skill_proficiencies
        );
    }
}
