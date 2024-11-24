<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Skill;
use App\Domain\Repository\SkillRepositoryInterface;

class SkillRepository extends AbstractBaseRepository implements SkillRepositoryInterface
{
    public function findById(int $id): ?Skill
    {
        return $this->find($id);
    }

    public function findByName(string $name): ?Skill
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findByTeacherId(int $teacher_id): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.teachers', 't')
            ->where('t.id = :teacher_id')
            ->setParameter('teacher_id', $teacher_id)
            ->getQuery()
            ->getResult();
    }

    public function findByStudentId(int $student_id): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.students', 'st')
            ->where('st.id = :student_id')
            ->setParameter('student_id', $student_id)
            ->getQuery()
            ->getResult();
    }
}
