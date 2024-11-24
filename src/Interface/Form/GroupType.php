<?php

declare(strict_types=1);

namespace App\Interface\Form;

use App\Domain\Entity\Group;
use App\Domain\Entity\Teacher;
use App\Domain\Repository\SkillRepositoryInterface;
use App\Domain\ValueObject\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GroupType extends AbstractType
{
    public function __construct(
        private readonly SkillRepositoryInterface $skill_repository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255]),
                ],
            ])
            ->add('teacher', EntityType::class, [
                'class' => Teacher::class,
                'choice_label' => fn (Teacher $teacher) => sprintf(
                    '%s %s (%s)',
                    $teacher->getFirstName(),
                    $teacher->getLastName(),
                    $teacher->getEmail()
                ),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('max_students', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 100]),
                ],
            ])
            ->add('required_skills', CollectionType::class, [
                'entry_type' => SkillProficiencyType::class,
                'entry_options' => [
                    'skill_choices' => $this->getSkillChoices(),
                    'level_choices' => $this->getSkillLevelChoices(),
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'csrf_protection' => false,
        ]);
    }

    private function getSkillChoices(): array
    {
        $skills = $this->skill_repository->findAll();
        $choices = [];
        foreach ($skills as $skill) {
            $choices[$skill->getName()] = $skill->getId();
        }
        return $choices;
    }

    private function getSkillLevelChoices(): array
    {
        $choices = [];
        foreach (SkillLevel::cases() as $level) {
            $choices[$level->getLabel()] = $level->value;
        }
        return $choices;
    }
}
