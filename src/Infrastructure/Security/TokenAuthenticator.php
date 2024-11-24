<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\DTO\Response\Security\AuthUser;
use App\Infrastructure\Security\Provider\TokenUserProvider;
use Exception;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
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

final class TokenAuthenticator extends AbstractAuthenticator
{
    private const HEADER = 'Authorization';
    private const PREFIX = 'Bearer ';

    public function __construct(
        private readonly JWTEncoderInterface $jwt_encoder,
        private readonly TokenUserProvider $token_user_provider,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER)
            && str_starts_with($request->headers->get(self::HEADER), self::PREFIX);
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $token = $this->extractToken($request);
            return $this->isJwt($token)
                ? $this->authenticateJwtToken($token)
                : $this->authenticateApiToken($token);
        } catch (InvalidArgumentException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['message' => $exception->getMessage()],
            Response::HTTP_UNAUTHORIZED
        );
    }

    private function extractToken(Request $request): string
    {
        $header = $request->headers->get(self::HEADER);
        if (!str_starts_with($header, self::PREFIX)) {
            throw new InvalidArgumentException('Invalid token format');
        }

        $token = substr($header, strlen(self::PREFIX));
        if (empty($token)) {
            throw new InvalidArgumentException('Token not provided');
        }

        return $token;
    }

    private function isJwt(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/', $token) === 1;
    }

    private function authenticateJwtToken(string $token): Passport
    {
        try {
            $payload = $this->jwt_encoder->decode($token);
            return new SelfValidatingPassport(
                new UserBadge($payload['username'], fn () => AuthUser::fromPayload($payload))
            );
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid JWT token: ' . $e->getMessage());
        }
    }

    private function authenticateApiToken(string $token): Passport
    {
        return new SelfValidatingPassport(
            new UserBadge(
                $token,
                function (string $token) {
                    $user = $this->token_user_provider->findByToken($token);
                    if ($user === null) {
                        throw new InvalidArgumentException('Invalid API token');
                    }
                    return $user;
                }
            )
        );
    }
}
