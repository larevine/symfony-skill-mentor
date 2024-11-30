<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

#[Route('/teachers/{id}/groups', name: 'web_teacher_groups', methods: ['GET', 'POST'])]
final class ManageGroupsAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly GroupServiceInterface $group_service,
        private readonly ProducerInterface $teacher_groups_producer
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $teacher = $this->teacher_service->findById(new EntityId($id));

        if ($teacher === null) {
            throw $this->createNotFoundException('Teacher not found');
        }

        if ($request->isMethod('POST')) {
            $group_ids = $request->request->all('groups');

            $this->teacher_groups_producer->publish(json_encode([
                'teacher_id' => $id,
                'group_ids' => $group_ids
            ]));

            $this->addFlash('success', 'Teacher groups update has been scheduled');

            return $this->redirectToRoute('web_teacher_list');
        }

        return $this->render('teacher/groups.html.twig', [
            'teacher' => $teacher,
            'all_groups' => $this->group_service->findAll(),
        ]);
    }
}
