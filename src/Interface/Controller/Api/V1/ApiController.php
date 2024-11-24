<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1;

use App\Interface\Exception\ApiException;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\GroupName;
use App\Domain\ValueObject\PersonName;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\SkillLevel;
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

    protected function createEntityId(int $id): EntityId
    {
        try {
            return new EntityId($id);
        } catch (DomainException $e) {
            throw ApiException::validationError($e->getMessage());
        }
    }

    protected function createGroupName(string $name): GroupName
    {
        try {
            return new GroupName($name);
        } catch (DomainException $e) {
            throw ApiException::validationError($e->getMessage());
        }
    }

    protected function createPersonName(string $first_name, string $last_name): PersonName
    {
        try {
            return new PersonName($first_name, $last_name);
        } catch (DomainException $e) {
            throw ApiException::validationError($e->getMessage());
        }
    }

    protected function createEmail(string $email): Email
    {
        try {
            return new Email($email);
        } catch (DomainException $e) {
            throw ApiException::validationError($e->getMessage());
        }
    }

    protected function createSkillLevel(string $level): SkillLevel
    {
        try {
            return SkillLevel::fromInt((int)$level);
        } catch (DomainException $e) {
            throw ApiException::validationError($e->getMessage());
        }
    }
}
