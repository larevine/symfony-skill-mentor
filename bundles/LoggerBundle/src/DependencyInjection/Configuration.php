<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree_builder = new TreeBuilder('otushomework_logger');
        $root_node = $tree_builder->getRootNode();

        $root_node
            ->children()
                ->booleanNode('debug_enabled')
                    ->defaultTrue()
                ->end()
                ->booleanNode('deprecation_logging_enabled')
                    ->defaultTrue()
                ->end()
                ->scalarNode('log_path')
                    ->defaultValue('%kernel.logs_dir%/app.log')
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_manager')
                            ->defaultValue('default')
                        ->end()
                        ->booleanNode('log_sql_queries')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('connection')
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('rabbitmq')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('connection')
                            ->defaultValue('default')
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('%env(RABBITMQ_HOST)%')
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue('%env(RABBITMQ_PORT)%')
                        ->end()
                        ->booleanNode('log_messages')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree_builder;
    }
}
