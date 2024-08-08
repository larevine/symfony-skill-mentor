<?php

declare(strict_types=1);

namespace App\Application\Service\HelloWorld;

class GreeterService
{
    private string $greet;

    public function __construct(string $greet)
    {
        $this->greet = $greet;
    }

    public function greet(string $name): string
    {
        return $this->greet . ', ' . $name . '!';
    }
}
