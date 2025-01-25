<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LoggerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        // Основные параметры логирования
        $container->setParameter('otushomework_logger.debug_enabled', $config['debug_enabled']);
        $container->setParameter('otushomework_logger.deprecation_logging_enabled', $config['deprecation_logging_enabled']);
        $container->setParameter('otushomework_logger.log_path', $config['log_path']);

        // Конфигурация Doctrine
        if (isset($config['doctrine'])) {
            $doctrine_config = $config['doctrine'];
            $container->setParameter('doctrine.dbal.default_connection', $doctrine_config['connection']);
            $container->setParameter('doctrine.orm.entity_manager.default', $doctrine_config['entity_manager']);

            if ($doctrine_config['log_sql_queries']) {
                $container->setParameter('doctrine.dbal.logging', true);
                $container->setParameter('doctrine.dbal.logger.class', 'Otushomework\LoggerBundle\Logger\NonDeprecationLogger');
            }
        }

        // Конфигурация RabbitMQ
        if (isset($config['rabbitmq'])) {
            $rabbit_config = $config['rabbitmq'];
            $container->setParameter('old_sound_rabbit_mq.connection.default.host', $rabbit_config['host']);
            $container->setParameter('old_sound_rabbit_mq.connection.default.port', $rabbit_config['port']);

            if ($rabbit_config['log_messages']) {
                $container->setParameter('old_sound_rabbit_mq.logger.class', 'Otushomework\LoggerBundle\Logger\NonDeprecationLogger');
            }
        }
    }
}
