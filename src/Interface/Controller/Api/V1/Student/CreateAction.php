<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\CreateStudentRequest;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students', methods: ['POST'])]
final class CreateAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateStudentRequest $request,
    ): JsonResponse {
        try {
            $student = $this->student_service->create(
                $request->first_name,
                $request->last_name,
                $request->email,
            );

            return $this->json(StudentResponse::fromEntity($student), Response::HTTP_CREATED);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
