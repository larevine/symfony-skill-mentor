<?php

declare(strict_types=1);

namespace App\Controller\Web\Form;

use App\Entity\Group;
use App\Entity\Skill;
use App\Entity\User;
use App\Service\Builder\GroupBuilderService;
use App\Service\Form\Type\GroupType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'groups')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $form_factory,
        private readonly EntityManagerInterface $em,
        private readonly GroupBuilderService $group_builder,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/form-create', name: 'create-group', methods: ['GET', 'POST'])]
    #[Route(path: '/form-update/{id}', name: 'update-group', methods: ['GET', 'POST'])]
    public function manageGroupAction(Request $request, string $_route, ?Group $group = null): Response
    {
        $is_route_update = $_route === 'update-group';

        $users = $this->em->getRepository(User::class)->findAll();
        $skills = $this->em->getRepository(Skill::class)->findAll();
        $form = $this->form_factory->create(GroupType::class, $group ?? null, [
            'is_route_update' => $is_route_update,
            'users' => $users,
            'skills' => $skills,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($is_route_update) {
                $this->group_builder->updateGroupWithRelatedEntities($group, $data);
            } else {
                $this->group_builder->saveGroupWithRelatedEntities($data);
            }
        }

        return $this->render('manage_group.html.twig', [
            'form' => $form,
            'is_route_update' => $is_route_update,
            'group' => $group,
        ]);
    }
}
