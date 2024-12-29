<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Tests\Fixtures\GroupFixtures;
use App\Tests\Fixtures\StudentFixtures;
use App\Tests\Fixtures\TeacherFixtures;
use App\Tests\Traits\FixturesTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractApiTestCase extends WebTestCase
{
    use FixturesTrait;

    protected KernelBrowser $client;
    protected ?string $auth_token = null;
    protected $em;
    protected UserPasswordHasherInterface $password_hasher;
    protected JWTTokenManagerInterface $jwt_manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->password_hasher = $container->get('security.user_password_hasher');
        $this->jwt_manager = $container->get(JWTTokenManagerInterface::class);

        // Clear database and load fixtures
        $this->clearDatabase($this->em);
        $this->loadBaseFixtures();

        // Create and authenticate admin user
        $admin = $this->createAdminUser();
        $this->auth_token = $this->jwt_manager->create($admin);
    }

    protected function loadBaseFixtures(): void
    {
        $this->loadTestFixtures([
            new TeacherFixtures(),
            new StudentFixtures(),
            new GroupFixtures(),
        ], $this->em, $this->password_hasher);
    }

    protected function createAdminUser(): Teacher
    {
        $admin = new Teacher('Admin', 'Admin', 'system.admin@example.com', 'password', ['ROLE_ADMIN'], 5);
        $admin->setPassword($this->password_hasher->hashPassword($admin, 'password'));

        $this->em->persist($admin);
        $this->em->flush();

        return $admin;
    }

    protected function createUser(string $email = 'test@example.com', string $role = 'ROLE_USER'): object
    {
        $user = match($role) {
            'ROLE_TEACHER' => new Teacher(
                'Test',
                'User',
                $email,
                'password',
                [$role],
                5
            ),
            default => new Student(
                'Test',
                'User',
                $email,
                'password',
                [$role]
            ),
        };

        $user->setPassword($this->password_hasher->hashPassword($user, 'password'));
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    protected function createTeacher(array $data = []): Teacher
    {
        return $this->createUser(
            $data['email'] ?? 'test@example.com',
            'ROLE_TEACHER'
        );
    }

    protected function createStudent(array $data = []): Student
    {
        return $this->createUser(
            $data['email'] ?? 'test@example.com',
            'ROLE_STUDENT'
        );
    }

    protected function jsonRequest(string $method, string $uri, array $data = []): void
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($this->auth_token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->auth_token;
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $server,
            $data ? json_encode($data) : null
        );
    }

    protected function getResponseContent(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function authenticateAs(string $reference): void
    {
        $user = $this->em->getRepository(Teacher::class)->findOneBy(['email' => $reference]);
        if ($user) {
            $this->auth_token = $this->jwt_manager->create($user);
        }
    }

    protected function getJwtToken(string $email = 'test@example.com', string $password = 'password'): ?string
    {
        $this->client->request(
            'POST',
            '/api/v1/auth/token',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => $password,
            ])
        );

        if ($this->client->getResponse()->isSuccessful()) {
            $data = $this->getResponseContent();
            return $data['token'] ?? null;
        }

        return null;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset sequences for all tables in PostgreSQL
        $connection = $this->em->getConnection();
        $sequences = [
            'users_id_seq',
            'skills_id_seq',
            'skill_proficiencies_id_seq',
            'groups_id_seq',
        ];

        foreach ($sequences as $sequence) {
            $connection->executeStatement(sprintf('ALTER SEQUENCE %s RESTART WITH 1', $sequence));
        }

        $this->em->close();
        $this->em = null;
    }
}
