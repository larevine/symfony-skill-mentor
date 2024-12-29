<?php

declare(strict_types=1);

namespace App\Interface\Controller\Web\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teachers/{id}/skills', name: 'web_teacher_skills', methods: ['GET', 'POST'])]
final class ManageSkillsAction extends AbstractController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SkillServiceInterface $skill_service,
        private readonly ProducerInterface $teacher_skills_producer
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $teacher = $this->teacher_service->findById(new EntityId($id));

        if ($teacher === null) {
            throw $this->createNotFoundException('Teacher not found');
        }

        if ($request->isMethod('POST')) {
            $skill_levels = $request->request->all('skill_levels');
            $skills = [];

            foreach ($skill_levels as $skill_id => $level) {
                if ($level > 0) {
                    $skills[] = [
                        'skill_id' => $skill_id,
                        'level' => match ((int)$level) {
                            1 => 'beginner',
                            2 => 'intermediate',
                            3, 4 => 'advanced',
                            5 => 'expert',
                            default => throw new \DomainException('Invalid skill level'),
                        }
                    ];
                }
            }

            $this->teacher_skills_producer->publish(json_encode([
                'teacher_id' => $id,
                'skills' => $skills
            ]));

            $this->addFlash('success', 'Teacher skills update has been scheduled');

            return $this->redirectToRoute('web_teacher_list');
        }

        $all_skills = $this->skill_service->findAll();
        $teacher_skills = [];
        foreach ($teacher->getSkills() as $skill_proficiency) {
            $teacher_skills[$skill_proficiency->getSkill()->getId()] = $skill_proficiency->getLevel();
        }

        return $this->render('teacher/manage_skills.html.twig', [
            'teacher' => $teacher,
            'all_skills' => $all_skills,
            'teacher_skills' => $teacher_skills,
        ]);
    }
}
