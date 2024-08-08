<?php

declare(strict_types=1);

namespace App\Application\Service\HelloWorld;

class FormatService
{
    private ?string $tag = null;

    public function __construct()
    {
        $this->tag = null;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function format(string $contents): string
    {
        return ($this->tag === null) ? $contents : "<{$this->tag}>$contents</{$this->tag}>";
    }
}
