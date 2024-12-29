<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1;

use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ApiController extends AbstractController
{
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        try {
            return parent::json($data, $status, $headers, $context);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }

    protected function validateEntityExists($entity, string $message): void
    {
        if (!$entity) {
            throw ApiException::notFound($message);
        }
    }
}
