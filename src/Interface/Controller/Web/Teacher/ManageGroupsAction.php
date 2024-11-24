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

#[Route('/teachers/{id}/groups', name: 'web_teacher_groups', methods: ['GET', 'POST'])]
final class ManageGroupsAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly GroupServiceInterface $group_service,
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
            $current_groups = $teacher->getTeachingGroups();

            // Remove old groups
            foreach ($current_groups as $group) {
                $this->teacher_service->removeFromGroup($teacher, $group);
            }

            // Add new groups
            foreach ($group_ids as $group_id) {
                $group = $this->group_service->findById(new EntityId((int)$group_id));
                if ($group !== null && $teacher->hasRequiredSkills($group)) {
                    $this->teacher_service->assignToGroup($teacher, $group);
                }
            }

            $this->addFlash('success', 'Teacher groups updated successfully');

            return $this->redirectToRoute('web_teacher_list');
        }

        return $this->render('teacher/groups.html.twig', [
            'teacher' => $teacher,
            'all_groups' => $this->group_service->findAll(),
        ]);
    }
}
