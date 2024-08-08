<?php

declare(strict_types=1);

namespace App\Application\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

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
        // Проверяем, что пространство имён существует
        if (!$schema->hasNamespace('public')) {
            $schema->createNamespace('public');
        }
    }
}
