<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}', methods: ['GET'])]
final class GetAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly TagAwareAdapterInterface $student_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $cache_key = 'student_' . $id;
            $cache_item = $this->student_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                return $this->json($cache_item->get());
            }

            $student_id = new EntityId($id);
            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $response = StudentResponse::fromEntity($student);

            $cache_item->set($response);
            $cache_item->tag(['students', 'student_' . $id]);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->student_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
