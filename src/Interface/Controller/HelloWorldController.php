<?php

declare(strict_types=1);

namespace App\Interface\Controller;

use App\Application\Service\HelloWorld\FormatService;
use App\Application\Service\HelloWorld\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер для тестирования функционала
 */
class HelloWorldController extends AbstractController
{
    public function __construct(
        private readonly FormatService $format_service,
        private readonly MessageService $message_service,
    ) {
    }

    public function hello(): Response
    {
        $message = $this->message_service->printMessages('world');
        $formatted_message = $this->format_service->format($message);

        return new Response('<html lang="en"><body>' . $formatted_message . '</body></html>');
    }
}
