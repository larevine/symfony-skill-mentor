<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}/students/{student_id}', methods: ['POST'])]
final class AddStudentAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly StudentServiceInterface $student_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(int $id, int $student_id): JsonResponse
    {
        try {
            $group_id = new EntityId($id);
            $student_id = new EntityId($student_id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            // Используем оба сервиса для поддержания консистентности
            $this->student_service->joinGroup($student, $group);
            $this->group_service->addStudent($group, $student);

            // Инвалидируем кэш группы и студента
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group',
                'id' => $group_id->getValue(),
            ]));
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'student',
                'id' => $student_id->getValue(),
            ]));

            return $this->json(GroupResponse::fromEntity(
                $this->group_service->findById($group_id)
            ), Response::HTTP_CREATED);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
