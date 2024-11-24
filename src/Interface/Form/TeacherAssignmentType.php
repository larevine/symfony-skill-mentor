<?php

declare(strict_types=1);

namespace App\Interface\Form;

use App\Domain\Entity\Group;
use App\Domain\Entity\Teacher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TeacherAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'csrf_protection' => false,
        ]);
    }
}
