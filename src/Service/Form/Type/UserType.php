<?php

declare(strict_types=1);

namespace App\Service\Form\Type;

use App\Entity\Enum\UserStatus;
use App\Entity\Role;
use App\Entity\Skill;
use App\Entity\User;
use App\Service\Form\DataMapper\UserDataMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email пользователя',
                'attr' => [
                    'data-time' => time(),
                    'placeholder' => 'Email пользователя',
                    'class' => 'user-email',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Имя',
            ])
            ->add('surname', TextType::class, [
                'label' => 'Фамилия',
            ]);

        if ($options['is_route_update'] ?? false) {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'Статус пользователя',
                    'choices' => [
                        UserStatus::ACTIVE->toString() => UserStatus::ACTIVE,
                        UserStatus::INACTIVE->toString() => UserStatus::INACTIVE,
                    ],
                    'data' => UserStatus::ACTIVE->toString(),
                ])
                ->add('roles', EntityType::class, [
                    'label' => 'Роли пользователя',
                    'class' => Role::class,
                    'choices' => $options['roles'],
                    'multiple' => true,
                    'expanded' => true,
                    'choice_label' => 'name',
                    'choice_value' => 'name',
                ])
                ->add('skills', EntityType::class, [
                    'label' => 'Навыки пользователя',
                    'class' => Skill::class,
                    'choices' => $options['skills'],
                    'multiple' => true,
                    'expanded' => false,
                    'choice_label' => 'fullname',
                    'choice_value' => 'id',
                ]);
        }

        $builder
            ->add('submit', SubmitType::class)
            ->setDataMapper(new UserDataMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'empty_data' => new User(),
            'is_route_update' => false,
            'roles' => [],
            'skills' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'save_user';
    }
}
