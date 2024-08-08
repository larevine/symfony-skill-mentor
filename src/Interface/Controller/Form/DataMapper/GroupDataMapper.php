<?php

declare(strict_types=1);

namespace App\Interface\Controller\Form\DataMapper;

use App\Application\DTO\ManageGroupDTO;
use App\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;
use Traversable;

readonly class GroupDataMapper implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['name']->setData($viewData->getName());
        $forms['limit_teachers']->setData($viewData->getLimitTeachers());
        $forms['limit_students']->setData($viewData->getLimitStudents());
        $forms['skill']->setData($viewData->getSkill());
        $forms['users']->setData($viewData->getUsers());
    }

    /**
     * @inheritDoc
     */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $users = isset($forms['users']) ? $forms['users']->getData() : [];
        if ($users instanceof ArrayCollection) {
            $user_ids = $users->map(static fn (User $user) => $user->getId())->toArray();
        } else {
            $user_ids = array_map(static fn (User $user) => $user->getId(), $users);
        }

        // remove duplicates
        $viewData = new ManageGroupDTO(
            name: $forms['name']->getData(),
            limit_teachers: $forms['limit_teachers']->getData(),
            limit_students: $forms['limit_students']->getData(),
            skill_id: $forms['skill']->getData()->getId(),
            level: $forms['skill']->getData()->getLevel(),
            user_ids: $user_ids
        );
    }
}
