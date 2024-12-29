<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}', methods: ['GET'])]
final class GetAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TagAwareAdapterInterface $group_pool,
        private readonly int $cache_ttl,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $cache_key = 'group_' . $id;
            $cache_item = $this->group_pool->getItem($cache_key);

            if ($cache_item->isHit()) {
                return $this->json($cache_item->get());
            }

            $group_id = new EntityId($id);
            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $response = GroupResponse::fromEntity($group);

            $cache_item->set($response);
            $cache_item->tag(['groups', 'group_' . $id]);
            $cache_item->expiresAfter($this->cache_ttl);
            $this->group_pool->save($cache_item);

            return $this->json($response);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
