<?php

declare(strict_types=1);

namespace App\Interface\Form;

use App\Domain\Entity\Skill;
use App\Domain\Entity\SkillProficiency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SkillProficiencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('level', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 5]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SkillProficiency::class,
            'csrf_protection' => false,
        ]);
    }
}
