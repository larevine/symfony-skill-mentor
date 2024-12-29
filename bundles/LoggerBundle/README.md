# LoggerBundle

Symfony бандл для расширенного логирования.

## Установка

1. Добавьте бандл в ваш проект через composer:
```bash
composer require otushomework/logger-bundle
```

2. Добавьте бандл в ядро приложения:
```php
// config/bundles.php
return [
    // ...
    Otushomework\LoggerBundle\LoggerBundle::class => ['all' => true],
];
```

3. Настройте бандл:
```yaml
# config/packages/otushomework_logger.yaml
otushomework_logger:
    # Основные настройки логирования
    debug_enabled: true
    deprecation_logging_enabled: true
    log_path: '%kernel.logs_dir%/app.log'
    
    # Настройка Doctrine
    doctrine:
        entity_manager: default
        connection: default
        log_sql_queries: true    # Включает логирование SQL-запросов
    
    # Настройка RabbitMQ
    rabbitmq:
        connection: default
        host: '%env(RABBITMQ_HOST)%'
        port: '%env(RABBITMQ_PORT)%'
        log_messages: true       # Включает логирование сообщений
```

## Использование

### Отладочное логирование
```php
use Otushomework\LoggerBundle\Logger\DebugLogger;

class YourService
{
    public function __construct(private DebugLogger $debugLogger) {}

    public function someMethod()
    {
        $this->debugLogger->log('Отладочное сообщение');
    }
}
```

### Обработка устаревших функций
```php
use Otushomework\LoggerBundle\Logger\DeprecationErrorHandler;

class YourService
{
    public function __construct(private DeprecationErrorHandler $deprecationHandler) {}

    public function handleError(array $error)
    {
        $this->deprecationHandler->handleDeprecation($error);
    }
}
```

### Общее логирование
```php
use Otushomework\LoggerBundle\Logger\NonDeprecationLogger;

class YourService
{
    public function __construct(private NonDeprecationLogger $logger) {}

    public function someMethod()
    {
        $this->logger->info('Информационное сообщение');
        $this->logger->warning('Предупреждение');
        $this->logger->error('Ошибка', ['контекст' => 'значение']);
    }
}
```

## Конфигурация других бандлов

### Doctrine
Бандл может настраивать Doctrine для логирования SQL-запросов:
```yaml
otushomework_logger:
    doctrine:
        entity_manager: custom_em  # Имя entity manager
        connection: custom_conn    # Имя подключения
        log_sql_queries: true     # Включает логирование SQL
```

### RabbitMQ
Бандл может настраивать RabbitMQ для логирования сообщений:
```yaml
otushomework_logger:
    rabbitmq:
        connection: custom_conn
        host: rabbitmq.example.com
        port: 5672
        log_messages: true        # Включает логирование сообщений
```

## Тестовое окружение

В тестовом окружении бандл автоматически переключается на использование тестовых логгеров, которые хранят логи в памяти вместо записи в файлы. Это упрощает проверку корректности логирования в тестах.

Пример теста:
```php
use Otushomework\LoggerBundle\Logger\Test\DummyDebugLogger;

class YourTest extends TestCase
{
    public function testLogging()
    {
        $logger = static::getContainer()->get(DebugLogger::class);
        assert($logger instanceof DummyDebugLogger);

        // Ваш код, который генерирует логи
        
        $logs = $logger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals('Ожидаемое сообщение', $logs[0]);
    }
}
```

## Особенности

1. Простая конфигурация через YAML файлы
2. Автоматическое переключение на тестовые логгеры в тестовом окружении
3. Поддержка различных уровней логирования
4. Возможность включения/отключения отдельных компонентов
5. Контекстное логирование с поддержкой дополнительных данных
6. Интеграция с Doctrine для логирования SQL-запросов
7. Интеграция с RabbitMQ для логирования сообщений
