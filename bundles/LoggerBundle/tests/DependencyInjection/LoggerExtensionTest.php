<?php

declare(strict_types=1);

namespace Otushomework\LoggerBundle\Tests\DependencyInjection;

use Otushomework\LoggerBundle\DependencyInjection\LoggerExtension;
use Otushomework\LoggerBundle\Logger\DebugLogger;
use Otushomework\LoggerBundle\Logger\NonDeprecationLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoggerExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private LoggerExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new LoggerExtension();
    }

    public function testDefaultConfiguration(): void
    {
        $config = [];
        $this->extension->load([$config], $this->container);

        $this->assertTrue($this->container->hasParameter('otushomework_logger.debug_enabled'));
        $this->assertTrue($this->container->hasParameter('otushomework_logger.deprecation_logging_enabled'));
        $this->assertTrue($this->container->hasParameter('otushomework_logger.log_path'));

        $this->assertTrue($this->container->getParameter('otushomework_logger.debug_enabled'));
        $this->assertTrue($this->container->getParameter('otushomework_logger.deprecation_logging_enabled'));
    }

    public function testCustomConfiguration(): void
    {
        $config = [
            'debug_enabled' => false,
            'deprecation_logging_enabled' => false,
            'log_path' => '/custom/path/app.log',
        ];

        $this->extension->load([$config], $this->container);

        $this->assertFalse($this->container->getParameter('otushomework_logger.debug_enabled'));
        $this->assertFalse($this->container->getParameter('otushomework_logger.deprecation_logging_enabled'));
        $this->assertEquals('/custom/path/app.log', $this->container->getParameter('otushomework_logger.log_path'));
    }
}
