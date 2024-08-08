<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Auth;

use App\Application\Security\Auth\Token\ApiAuthService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP Basic Authentication
 * Авторизация пользователя по email и паролю
 */
#[Route(path: '/api/v1/api-token')]
class ApiTokenController extends AbstractController
{
    public function __construct(
        private readonly ApiAuthService $auth_service
    ) {
    }

    /**
     * @throws RandomException
     */
    #[Route(path: '', methods: ['POST'])]
    public function getTokenAction(Request $request): Response
    {
        $user = $request->getUser();
        $password = $request->getPassword();
        if ($user === null || $password === null) {
            return new JsonResponse(['message' => 'Authorization required'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->auth_service->isCredentialsValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid password or username'], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['token' => $this->auth_service->getToken($user)]);
    }
}
