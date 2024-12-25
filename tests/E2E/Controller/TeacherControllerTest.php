<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Domain\Entity\Teacher;
use App\Tests\Fixtures\TeacherFixtures;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Response;

class TeacherControllerTest extends AbstractApiTestCase
{
    private TagAwareAdapterInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->cache = static::getContainer()->get(TagAwareAdapterInterface::class);
    }

    public function testCreateTeacher(): void
    {
        $teacher_data = [
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'email' => 'new.teacher1@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_TEACHER'],
            'max_groups' => 5
        ];

        $this->jsonRequest('POST', '/api/v1/teachers', $teacher_data);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->getResponseContent();
        self::assertArrayHasKey('id', $data);
        self::assertEquals($teacher_data['email'], $data['email']);
    }

    public function testGetTeacher(): void
    {
        // Use fixture data
        $teacher_data = TeacherFixtures::JOHN_TEACHER;

        // Find the teacher by email
        $teacher = $this->em->getRepository(Teacher::class)->findOneBy(['email' => $teacher_data['email']]);
        self::assertNotNull($teacher, 'Teacher fixture not found');

        $this->jsonRequest('GET', "/api/v1/teachers/{$teacher->getId()}");

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseContent();
        self::assertEquals($teacher_data['email'], $data['email']);
        self::assertEquals($teacher_data['first_name'], $data['first_name']);
        self::assertEquals($teacher_data['last_name'], $data['last_name']);
    }

    public function testDeleteTeacher(): void
    {
        $teacher_id = 1;
        $this->jsonRequest('DELETE', '/api/v1/teachers/' . $teacher_id);

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id]);

        // Verify teacher is deleted
        $this->jsonRequest('GET', '/api/v1/teachers/' . $teacher_id);
        self::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateTeacher(): void
    {
        $teacher_id = 1;
        $update_data = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'max_groups' => 10
        ];

        $this->jsonRequest('PUT', '/api/v1/teachers/' . $teacher_id, $update_data);

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->cache->invalidateTags(['teachers', 'teacher_' . $teacher_id]);

        $data = $this->getResponseContent();
        self::assertEquals($update_data['first_name'], $data['first_name']);
        self::assertEquals($update_data['last_name'], $data['last_name']);
        self::assertEquals($update_data['max_groups'], $data['max_groups']);
    }

    public function testSearchTeachers(): void
    {
        $this->jsonRequest('GET', '/api/v1/teachers?search=John');

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseContent();
        self::assertIsArray($data);
        self::assertArrayHasKey('items', $data);
        self::assertGreaterThan(0, count($data['items']));

        foreach ($data['items'] as $teacher) {
            self::assertStringContainsString('John', $teacher['first_name']);
        }
    }
}
