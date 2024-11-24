<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teachers/{id}/delete', name: 'web_teacher_delete', methods: ['POST'])]
final class DeleteAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $teacher = $this->teacher_service->findById(new EntityId($id));

        if ($teacher === null) {
            throw $this->createNotFoundException('Teacher not found');
        }

        $this->teacher_service->delete($teacher);

        $this->addFlash('success', 'Teacher deleted successfully');

        return $this->redirectToRoute('web_teacher_list');
    }
}
