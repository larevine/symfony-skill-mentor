<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\ListResponse;
use App\Interface\DTO\StudentResponse;
use App\Interface\DTO\Filter\StudentFilterRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly TagAwareAdapterInterface $student_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(
        #[MapQueryString] StudentFilterRequest $filter,
    ): JsonResponse {
        try {
            $cache_key = 'student_list_' . md5(serialize($filter));
            $cache_item = $this->student_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                return $this->json($cache_item->get());
            }

            $students = $this->student_service->findByFilter($filter);
            $total = count($students);

            $response = ListResponse::create(
                items: array_map(
                    static fn ($student) => StudentResponse::fromEntity($student),
                    $students,
                ),
                total: $total,
                page: $filter->getPage(),
                per_page: $filter->getPerPage(),
            );

            $cache_item->set($response);
            $cache_item->tag(['students']);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->student_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
