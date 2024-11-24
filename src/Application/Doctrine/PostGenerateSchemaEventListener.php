<?php

declare(strict_types=1);

namespace App\Application\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Doctrine\DBAL\Schema\SchemaException;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema, connection: 'default')]
class PostGenerateSchemaEventListener
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return ['postGenerateSchema'];
    }

    /**
     * @throws SchemaException
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        if ($schema->getName() !== 'public') {
            $schema->createNamespace('public');
        }
    }
}
