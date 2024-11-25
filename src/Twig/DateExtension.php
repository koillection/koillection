<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DateExtension extends AbstractExtension
{
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('timeAgo', [DateRuntime::class, 'timeAgo']),
            new TwigFilter('timeDiff', [DateRuntime::class, 'timeDiff']),
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getValidationRegexForDateFormat', [DateRuntime::class, 'getValidationRegexForDateFormat'])
        ];
    }
}
