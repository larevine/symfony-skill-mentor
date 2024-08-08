<?php

declare(strict_types=1);

namespace App\Application\Symfony;

use App\Application\Service\HelloWorld\MessageService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Вызывается в Kernel
 *
 * Добавляет все сервисы с тегом app.greeter_service в сервис MessageService, учитывая их приоритет.
 */
class GreeterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(MessageService::class)) {
            return;
        }
        $message_service = $container->findDefinition(MessageService::class);
        $greeter_services = $container->findTaggedServiceIds('app.greeter_service');
        uasort($greeter_services, static fn (array $tag1, array $tag2) => $tag1[0]['priority'] - $tag2[0]['priority']);
        foreach ($greeter_services as $id => $tags) {
            // После сортировки и перебора всех тегированных сервисов добавляется метод вызова addGreeter в
            // MessageService. В этот метод передается ссылка на каждый сервис, найденный по тегу. Это позволяет
            // MessageService получить доступ ко всем сервисам, которые были помечены тегом app.greeter_service,
            // в порядке их приоритетов.
            $message_service->addMethodCall('addGreeter', [new Reference($id)]);
        }
    }
}
