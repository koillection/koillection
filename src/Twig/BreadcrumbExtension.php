<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('buildBreadcrumb', [BreadcrumbRuntime::class, 'buildBreadcrumb']),
            new TwigFunction('renderBreadcrumb', [BreadcrumbRuntime::class, 'renderBreadcrumb'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
}
