<?php

declare(strict_types=1);

namespace App\Application\Service\HelloWorld;

class MessageService
{
    /** @var GreeterService[] */
    private array $greeter_services;
    /** @var FormatService[] */
    private array $format_services;

    public function __construct()
    {
        $this->greeter_services = [];
        $this->format_services = [];
    }

    public function addGreeter(GreeterService $greeter_service): void
    {
        $this->greeter_services[] = $greeter_service;
    }

    public function addFormatter(FormatService $format_service): void
    {
        $this->format_services[] = $format_service;
    }

    public function printMessages(string $name): string
    {
        $result = '';
        foreach ($this->greeter_services as $greeter_service) {
            $current = $greeter_service->greet($name);
            foreach ($this->format_services as $format_service) {
                $current = $format_service->format($current);
            }
            $result .= $current;
        }

        return $result;
    }
}
