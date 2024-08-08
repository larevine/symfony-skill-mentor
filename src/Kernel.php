<?php

declare(strict_types=1);

namespace App;

use App\Application\Symfony\FormatterPass;
use App\Application\Symfony\GreeterPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // используется для добавления компиляторских проходов в контейнер Symfony
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FormatterPass());
        $container->addCompilerPass(new GreeterPass());
    }
}
