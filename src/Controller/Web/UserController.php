<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $user_manager,
    ) {
    }

    #[Route(path: '', methods: ['GET'])]
    public function getUsersAction(Request $request): Response
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $users = $this->user_manager->getUsers($page ?? 0, $per_page ?? 20);
        $users = array_map(static fn (User $user) => $user->toArray(), $users);

        return $this->render(
            view: 'vue/users.html.twig',
            parameters: ['users' => json_encode($users, JSON_THROW_ON_ERROR)]
        );
    }
}
