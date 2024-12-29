<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use App\Tests\Fixtures\AbstractUserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait FixturesTrait
{
    protected function loadTestFixtures(array $fixtures, EntityManagerInterface $em, ?UserPasswordHasherInterface $hasher = null): void
    {
        if ($hasher !== null) {
            AbstractUserFixture::setPasswordHasher($hasher);
        }

        $loader = new Loader();
        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function clearDatabase(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();
        $schema_manager = $connection->createSchemaManager();

        // Drop all tables if they exist
        $tables = [
            'student_groups',
            'skill_proficiencies',
            'skills',
            'groups',
            'users'
        ];

        foreach ($tables as $table) {
            if ($schema_manager->tablesExist([$table])) {
                $connection->executeStatement("DROP TABLE IF EXISTS $table CASCADE");
            }
        }

        // Create schema from entities
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->createSchema($metadatas);
    }
}
