<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VisibilityExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getVisibilityReason', [VisibilityRuntime::class, 'getVisibilityReason']),
        ];
    }
}
