<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\DTO\UpdatePasswordRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{teacher_id}/password', methods: ['PUT'])]
final class UpdatePasswordAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly UserPasswordHasherInterface $password_hasher,
    ) {
    }

    public function __invoke(
        int $teacher_id,
        #[MapRequestPayload] UpdatePasswordRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($teacher_id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            // Verify current password
            if (!$this->password_hasher->isPasswordValid($teacher, $request->current_password)) {
                throw new DomainException('Current password is incorrect');
            }

            // Hash and set new password
            $hashedPassword = $this->password_hasher->hashPassword($teacher, $request->new_password);
            $teacher->setPassword($hashedPassword);

            $this->teacher_service->save($teacher);

            return $this->json(TeacherResponse::fromEntity($teacher));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
