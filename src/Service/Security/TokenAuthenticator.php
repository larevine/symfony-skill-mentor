<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Manager\UserManager;
use App\Service\Security\Auth\AuthUser;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Придуманный пример для homework
 * От бизнеса поступила задача сделать JWT авторизацию, но при этом оставить на время базовую (API токен)
 */
class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly JWTEncoderInterface $jwt_encoder,
        private readonly UserManager $user_manager,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->extractToken($request);
        if ($this->isJwt($token)) {
            return $this->authenticateJwtToken($token);
        }

        return $this->authenticateApiToken($token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => 'Invalid JWT Token'], Response::HTTP_FORBIDDEN);
    }

    private function extractToken(Request $request): ?string
    {
        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');
        $token = $extractor->extract($request);

        if ($token === false) {
            throw new CustomUserMessageAuthenticationException('No API token was provided');
        }

        return $token;
    }

    private function isJwt($token): bool
    {
        return preg_match('/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/', $token) === 1;
    }

    private function authenticateJwtToken(string $token): Passport
    {
        $token_data = $this->jwt_encoder->decode($token);
        if (!isset($token_data['username'])) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT token');
        }

        return new SelfValidatingPassport(
            new UserBadge($token_data['username'], fn () => new AuthUser($token_data))
        );
    }

    private function authenticateApiToken(string $token): SelfValidatingPassport
    {
        return new SelfValidatingPassport(
            new UserBadge($token, fn ($token) => $this->user_manager->findUserByToken($token))
        );
    }
}
