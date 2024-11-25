<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Intl\Locales;

class LocaleHelper
{
    public function __construct(
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
        #[Autowire(param: 'kernel.enabled_locales')] private readonly array $enabledLocales
    ) {
    }

    public function getLocaleLabels(): array
    {
        $languages = [];

        foreach ($this->enabledLocales as $locale) {
            $languages[$locale] = ucfirst(Locales::getName($locale, $locale));
        }

        asort($languages);

        return $languages;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
}
