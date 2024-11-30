<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupFilterRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\DTO\ListResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TagAwareAdapterInterface $group_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(
        #[MapQueryString] GroupFilterRequest $filter,
    ): JsonResponse {
        try {
            $cache_key = 'group_list_' . md5(serialize($filter));
            $cache_item = $this->group_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                return $this->json($cache_item->get());
            }

            $groups = $this->group_service->findByFilter($filter);
            $total = $this->group_service->countByFilter($filter);

            $response = ListResponse::create(
                items: array_map(
                    static fn ($group) => GroupResponse::fromEntity($group),
                    $groups,
                ),
                total: $total,
                page: $filter->page,
                per_page: $filter->per_page,
            );

            $cache_item->set($response);
            $cache_item->tag(['groups']);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->group_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
