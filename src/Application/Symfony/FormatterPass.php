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
 * CompilerPassInterface предназначен для выполнения определенной логики во время фазы компиляции
 * контейнера служб Symfony, выполняется во время компиляции контейнера служб Symfony
 *
 * Выполняется только один раз при сборке контейнера (например, при первом запуске или после изменения конфигурации
 * сервисов)
 *
 * Цель этого компиляторского прохода — динамически настраивать контейнер, добавляя форматтеры в MessageService на
 * основе тегов. Это позволяет автоматически связывать определенные сервисы с нужными методами.
 *
 * Не влияют на производительность приложения в процессе его выполнения
 */
class FormatterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Проверка наличия сервиса
        if (!$container->has(MessageService::class)) {
            return;
        }
        // Получение определения сервиса
        $message_service = $container->findDefinition(MessageService::class);
        // Поиск тегированных сервисов
        $formatter_services = $container->findTaggedServiceIds('app.formatter_service');
        // Для каждого тегированного сервиса вызываем метод addFormatter у MessageService, передавая в него ссылку
        // на форматтер-сервис.
        foreach ($formatter_services as $id => $tags) {
            $message_service->addMethodCall('addFormatter', [new Reference($id)]);
        }
    }
}
