<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Tests\Fixtures\StudentFixtures;
use Symfony\Component\HttpFoundation\Response;

class StudentControllerTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    public function testCreateStudent(): void
    {
        $new_email = 'student@example.com';
        $student_data = StudentFixtures::JOHN_STUDENT;

        $this->jsonRequest(
            'POST',
            '/api/v1/students',
            [
                'first_name' => $student_data['first_name'],
                'last_name' => $student_data['last_name'],
                'email' => $new_email,
                'password' => $student_data['password'],
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('id', $data);
        self::assertEquals($student_data['first_name'], $data['first_name']);
        self::assertEquals($student_data['last_name'], $data['last_name']);
        self::assertEquals($new_email, $data['email']);
    }

    public function testGetStudent(): void
    {
        $student_data = StudentFixtures::JOHN_STUDENT;
        $student_id = 4;

        $this->jsonRequest('GET', "/api/v1/students/{$student_id}");

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertEquals($student_data['first_name'], $data['first_name']);
        self::assertEquals($student_data['last_name'], $data['last_name']);
        self::assertEquals($student_data['email'], $data['email']);
    }

    public function testUpdateStudent(): void
    {
        $student_id = 8;
        $updated_data = [
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'email' => 'updated.student@example.com',
        ];

        $this->jsonRequest(
            'PUT',
            "/api/v1/students/{$student_id}",
            $updated_data
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertEquals($updated_data['first_name'], $data['first_name']);
        self::assertEquals($updated_data['last_name'], $data['last_name']);
        self::assertEquals($updated_data['email'], $data['email']);
    }

    public function testCreateDuplicateEmail(): void
    {
        $student_data = StudentFixtures::JOHN_STUDENT;

        $this->jsonRequest(
            'POST',
            '/api/v1/students',
            [
                'first_name' => 'Another',
                'last_name' => 'Student',
                'email' => $student_data['email'],
                'password' => 'password',
            ]
        );

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchStudents(): void
    {
        $this->jsonRequest('GET', '/api/v1/students?search=John');

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertGreaterThan(0, count($data['items']));
        foreach ($data['items'] as $student) {
            self::assertStringContainsString('John', $student['first_name']);
        }
    }
}
