<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teachers/{id}/edit', name: 'web_teacher_edit', methods: ['GET', 'POST'])]
final class EditAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $teacher = $this->teacher_service->findById(new EntityId($id));

        if ($teacher === null) {
            throw $this->createNotFoundException('Teacher not found');
        }

        if ($request->isMethod('POST')) {
            $this->teacher_service->update(
                teacher: $teacher,
                first_name: $request->request->get('first_name'),
                last_name: $request->request->get('last_name'),
                email: $request->request->get('email'),
                max_groups: $teacher->getMaxGroups(),
            );

            $this->addFlash('success', 'Teacher updated successfully');

            return $this->redirectToRoute('web_teacher_list');
        }

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
        ]);
    }
}
