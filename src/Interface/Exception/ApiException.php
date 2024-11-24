<?php

declare(strict_types=1);

namespace App\Interface\Exception;

use DomainException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    public static function fromDomainException(DomainException $e, int $statusCode = 400): self
    {
        return new self($statusCode, $e->getMessage(), $e);
    }

    public static function validationError(string $message): self
    {
        return new self(400, $message);
    }

    public static function badRequest(string $message): self
    {
        return new self(400, $message);
    }

    public static function notFound(string $message): self
    {
        return new self(404, $message);
    }

    public static function conflict(string $message): self
    {
        return new self(409, $message);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(401, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(403, $message);
    }
}
