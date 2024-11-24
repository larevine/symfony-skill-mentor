<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/teachers', name: 'web_teacher_list', methods: ['GET'])]
final class ListAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $teachers = $this->teacher_service->findAll();
        if ($request->query->getBoolean('vue')) {
            return $this->render('teacher/list_vue.html.twig', [
                'teachers_json' => $this->serializer->serialize($teachers, 'json', [
                    'groups' => ['teacher:read'],
                    'circular_reference_handler' => function ($object) {
                        return $object->getId();
                    }
                ])
            ]);
        }

        return $this->render('teacher/list.html.twig', [
            'teachers' => $teachers,
        ]);
    }
}
