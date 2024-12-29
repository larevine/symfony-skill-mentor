<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\DTO\UpdatePasswordRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{student_id}/password', methods: ['PUT'])]
final class UpdatePasswordAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function __invoke(
        int $student_id,
        #[MapRequestPayload] UpdatePasswordRequest $request,
    ): JsonResponse {
        try {
            $student_id = new EntityId($student_id);
            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            // Verify current password
            if (!$this->password_hasher->isPasswordValid($student, $request->current_password)) {
                throw new DomainException('Current password is incorrect');
            }

            // Hash and set new password
            $hashedPassword = $this->password_hasher->hashPassword($student, $request->new_password);
            $student->setPassword($hashedPassword);

            $this->student_service->save($student);

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
