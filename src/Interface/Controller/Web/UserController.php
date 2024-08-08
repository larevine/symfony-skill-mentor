<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web;

use App\Application\Service\UserService;
use App\Domain\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $user_service,
    ) {
    }

    /**
     * @throws \JsonException
     */
    #[Route(path: '', methods: ['GET'])]
    public function getUsersAction(Request $request): Response
    {
        $per_page = $request->query->get('per_page');
        $page = $request->query->get('page');
        $users = $this->user_service->getUsers($page ?? 0, $per_page ?? 20);
        $users = array_map(static fn (User $user) => $user->toArray(), $users);

        return $this->render(
            view: 'vue/users.html.twig',
            parameters: ['users' => json_encode($users, JSON_THROW_ON_ERROR)]
        );
    }
}
