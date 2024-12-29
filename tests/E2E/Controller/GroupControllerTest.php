<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Tests\Fixtures\GroupFixtures;
use App\Tests\Fixtures\StudentFixtures;
use App\Tests\Fixtures\TeacherFixtures;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    public function testCreateGroup(): void
    {
        $teacher_id = 1;
        $group_data = [
            'name' => 'Test Group',
            'min_students' => 5,
            'max_size' => 15,
            'teacher_id' => $teacher_id,
            'required_skills' => []
        ];

        $this->jsonRequest(
            'POST',
            '/api/v1/groups',
            $group_data
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('id', $data);
        self::assertSame($group_data['name'], $data['name']);
        self::assertSame($group_data['min_students'], $data['min_students']);
        self::assertSame($group_data['max_size'], $data['max_students']);
    }

    public function testGetGroup(): void
    {
        $group_id = 1;
        $group_data = GroupFixtures::MATH_GROUP;

        // Then get the group
        $this->jsonRequest('GET', "/api/v1/groups/{$group_id}");

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertSame($group_data['name'], $data['name']);
        self::assertSame($group_data['min_students'], $data['min_students']);
        self::assertSame($group_data['max_size'], $data['max_students']);
    }

    public function testAssignTeacher(): void
    {
        $group_id = 1;
        $teacher_id = 2;
        $new_teacher = TeacherFixtures::JANE_TEACHER;

        // Assign teacher to group
        $this->jsonRequest(
            'POST',
            "/api/v1/groups/{$group_id}/teacher/{$teacher_id}"
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertSame($new_teacher['first_name'], $data['teacher']['first_name']);
        self::assertSame($new_teacher['last_name'], $data['teacher']['last_name']);
    }

    public function testAddStudent(): void
    {
        $group_id = 1;
        $student_id = 5;
        $new_student = StudentFixtures::JANE_STUDENT;

        // Add student to group
        $this->jsonRequest(
            'POST',
            "/api/v1/groups/{$group_id}/students/{$student_id}",
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertCount(1, $data['students']);
        self::assertSame($new_student['first_name'], $data['students'][0]['first_name']);
        self::assertSame($new_student['last_name'], $data['students'][0]['last_name']);
    }

    public function testAddStudentToFullGroup(): void
    {
        // Create a group with max_size of 1
        $group_data = [
            'name' => 'Small Group',
            'min_students' => 1,
            'max_size' => 2,
            'teacher_id' => 1, // ID учителя из TeacherFixtures
            'required_skills' => []
        ];

        $this->jsonRequest(
            'POST',
            '/api/v1/groups',
            $group_data
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode(), json_encode($data));
        self::assertArrayHasKey('id', $data);
        $group_id = $data['id'];

        $student1_id = 4;
        $student2_id = 5;

        // Add first student to group
        $this->jsonRequest(
            'POST',
            "/api/v1/groups/{$group_id}/students/{$student1_id}",
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), json_encode($data));

        // Try to add second student to group - should fail
        $this->jsonRequest(
            'POST',
            "/api/v1/groups/{$group_id}/students/{$student2_id}",
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('type', $data);
        self::assertArrayHasKey('title', $data);
        self::assertStringContainsString('An error occurred', $data['title']);
    }

    public function testSearchGroups(): void
    {
        // Search for groups
        $this->jsonRequest('GET', '/api/v1/groups?search=Math');

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertIsArray($data);
        self::assertCount(3, $data['items']);
        self::assertSame('Math Group', $data['items'][0]['name']);
    }
}
