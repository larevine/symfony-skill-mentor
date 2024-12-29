<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{teacher_id}', methods: ['GET'])]
final class GetAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly TagAwareAdapterInterface $teacher_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(int $teacher_id): JsonResponse
    {
        try {
            $cache_key = 'teacher_' . $teacher_id;
            $cache_item = $this->teacher_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                return $this->json($cache_item->get());
            }

            $teacher_id = new EntityId($teacher_id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $response = TeacherResponse::fromEntity($teacher);

            $cache_item->set($response);
            $cache_item->tag(['teachers', 'teacher_' . $teacher_id->getValue()]);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->teacher_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
