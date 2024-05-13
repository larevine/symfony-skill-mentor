<?php

declare(strict_types=1);

namespace App\Controller\Api\V1\Auth;

use App\Service\Security\Auth\Token\JWTAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/jwt-token')]
class JWTTokenController extends AbstractController
{
    public function __construct(
        private readonly JWTAuthService $JWT_auth_service
    ) {
    }

    #[Route(path: '', methods: ['POST'])]
    public function getTokenAction(Request $request): Response
    {
        $user = $request->getUser();
        $password = $request->getPassword();
        if (is_null($user) || is_null($password)) {
            return new JsonResponse(['message' => 'Authorization required'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->JWT_auth_service->isCredentialsValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid password or username'], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['token' => $this->JWT_auth_service->getToken($user)]);
    }
}
