<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\ListResponse;
use App\Interface\DTO\TeacherResponse;
use App\Interface\DTO\Filter\TeacherFilterRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly TagAwareAdapterInterface $teacher_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(
        #[MapQueryString] TeacherFilterRequest $filter,
    ): JsonResponse {
        try {
            $cache_key = 'teacher_list_' . md5(serialize($filter));
            $cache_item = $this->teacher_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                error_log('Cache hit for key: ' . $cache_key);
                return $this->json($cache_item->get());
            }

            error_log('Cache miss for key: ' . $cache_key);

            $teachers = $this->teacher_service->findByFilter($filter);
            $total = count($teachers);

            $response = ListResponse::create(
                items: array_map(
                    static fn ($teacher) => TeacherResponse::fromEntity($teacher),
                    $teachers,
                ),
                total: $total,
                page: $filter->getPage(),
                per_page: $filter->getPerPage(),
            );

            $cache_item->set($response);
            $cache_item->tag(['teachers']);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->teacher_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
