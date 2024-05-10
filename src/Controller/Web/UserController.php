<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Role;
use App\Entity\Skill;
use App\Entity\User;
use App\Service\Builder\UserBuilderService;
use App\Service\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $form_factory,
        private readonly EntityManagerInterface $em,
        private readonly UserBuilderService $user_builder,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/form-create', name: 'create-user', methods: ['GET', 'POST'])]
    #[Route(path: '/form-update/{id}', name: 'update-user', methods: ['GET', 'POST'])]
    public function manageUserAction(Request $request, string $_route, ?User $user = null): Response
    {
        $is_route_update = $_route === 'update-user';

        $roles = $this->em->getRepository(Role::class)->findAll();
        $skills = $this->em->getRepository(Skill::class)->findAll();
        $form = $this->form_factory->create(UserType::class, $user ?? null, [
            'is_route_update' => $is_route_update,
            'roles' => $roles,
            'skills' => $skills,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($is_route_update) {
                $this->user_builder->updateUserWithRelatedEntities($user, $data);
            } else {
                $this->user_builder->saveUserWithRelatedEntities($data);
            }
        }

        return $this->render('manage_user.html.twig', [
            'form' => $form,
            'is_route_update' => $is_route_update,
            'user' => $user,
        ]);
    }
}
