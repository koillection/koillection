<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DiskUsageExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSpaceUsedByUser', [DiskUsageRuntime::class, 'getSpaceUsedByUser']),
            new TwigFunction('getSpaceUsedByUsers', [DiskUsageRuntime::class, 'getSpaceUsedByUsers']),
        ];
    }
}
