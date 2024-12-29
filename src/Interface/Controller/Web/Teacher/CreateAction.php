<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Interface\Form\CreateTeacherType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teachers/create', name: 'web_teacher_create', methods: ['GET', 'POST'])]
final class CreateAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CreateTeacherType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->teacher_service->create(
                first_name: $data['first_name'],
                last_name: $data['last_name'],
                email: $data['email'],
                max_groups: $data['max_groups'],
            );

            $this->addFlash('success', 'Teacher created successfully');

            return $this->redirectToRoute('web_teacher_list');
        }

        return $this->render('teacher/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
