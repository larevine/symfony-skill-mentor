<?php

declare(strict_types=1);

namespace App\Service\Form\DataMapper;

use App\DTO\ManageUserDTO;
use App\Entity\Enum\UserStatus;
use App\Entity\Skill;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;
use Traversable;

readonly class UserDataMapper implements DataMapperInterface
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

        $forms['email']->setData($viewData->getEmail());
        $forms['name']->setData($viewData->getName());
        $forms['surname']->setData($viewData->getSurname());
        $forms['status']->setData($viewData->getStatus());
        $forms['roles']->setData($viewData->getRoles());
        $forms['skills']->setData($viewData->getSkills());
    }

    /**
     * @inheritDoc
     */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $roles = isset($forms['roles']) ? $forms['roles']->getData() : [];
        $skills = isset($forms['skills']) ? $forms['skills']->getData() : [];
        if ($skills instanceof ArrayCollection) {
            $skill_ids = $skills->map(static fn (Skill $skill) => $skill->getId())->toArray();
        } else {
            $skill_ids = array_map(static fn (Skill $skill) => $skill->getId(), $skills);
        }

        $viewData = new ManageUserDTO(
            email: $forms['email']->getData(),
            password: $forms['password']->getData() ?? null,
            name: $forms['name']->getData(),
            surname: $forms['surname']->getData(),
            status: isset($forms['status']) ? $forms['status']->getData() : UserStatus::ACTIVE,
            roles: $roles,
            skill_ids: $skill_ids,
        );
    }
}
