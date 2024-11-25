<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('safeContent', [AppRuntime::class, 'safeContent'], ['is_safe' => ['html']]),
            new TwigFilter('bytes', [AppRuntime::class, 'bytes']),
            new TwigFilter('highlightTags', [AppRuntime::class, 'highlightTags'], ['is_safe' => ['html']]),
            new TwigFilter('jsonDecode', [AppRuntime::class, 'jsonDecode']),
            new TwigFilter('base64Encode', [AppRuntime::class, 'base64Encode']),
            new TwigFilter('mimetype', [AppRuntime::class, 'mimetype']),
            new TwigFilter('unique', [AppRuntime::class, 'unique']),
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderTitle', [AppRuntime::class, 'renderTitle']),
            new TwigFunction('getUnderlinedTags', [AppRuntime::class, 'getUnderlinedTags'], ['is_safe' => ['html']]),
            new TwigFunction('isFeatureEnabled', [AppRuntime::class, 'isFeatureEnabled']),
            new TwigFunction('fileSize', [AppRuntime::class, 'fileSize']),
            new TwigFunction('getDefaultLightThemeColors', [AppRuntime::class, 'getDefaultLightThemeColors']),
            new TwigFunction('getDefaultDarkThemeColors', [AppRuntime::class, 'getDefaultDarkThemeColors']),
            new TwigFunction('getConfigurationValue', [AppRuntime::class, 'getConfigurationValue']),
        ];
    }
}
