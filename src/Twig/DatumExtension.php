<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DatumExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getListValuesFromDatumLabelAndType', [DatumRuntime::class, 'getListValuesFromDatumLabelAndType']),
        ];
    }
}
