<?php

declare(strict_types=1);

namespace App\Service\Form\Type;

use App\Entity\Group;
use App\Entity\Skill;
use App\Entity\User;
use App\Service\Form\DataMapper\GroupDataMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название группы',
                'required' => true,
            ])
            ->add('limit_teachers', IntegerType::class, [
                'label' => 'Лимит учителей',
                'required' => false,
            ])
            ->add('limit_students', IntegerType::class, [
                'label' => 'Лимит учеников',
                'required' => false,
            ])
            ->add('skill', EntityType::class, [
                'label' => 'Навык',
                'class' => Skill::class,
                'choices' => $options['skills'],
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'fullname',
                'choice_value' => 'fullname',
                'required' => true,
            ])
            ->add('users', EntityType::class, [
                'label' => 'Участники группы',
                'class' => User::class,
                'choices' => $options['users'],
                'multiple' => true,
                'expanded' => false,
                'choice_label' => 'fullname',
                'choice_value' => 'id',
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
            ->setDataMapper(new GroupDataMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'empty_data' => new Group(),
            'is_route_update' => false,
            'skills' => [],
            'users' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'save_group';
    }
}
