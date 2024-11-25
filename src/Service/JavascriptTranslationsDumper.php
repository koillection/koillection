<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Twig\Environment;

class JavascriptTranslationsDumper
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
        #[Autowire(param: 'kernel.enabled_locales')] private readonly array $enabledLocales,
        #[Autowire('%kernel.project_dir%/translations')] private readonly string $translationsPath,
    ) {
    }

    public function dump(string $target): void
    {
        $pattern = '/translations/{domain}.{_format}';

        $parts = array_filter(explode('/', $pattern));
        $this->filesystem->remove($target . '/' . current($parts));

        $this->dumpConfig($pattern, $target);

        $this->dumpTranslationsPerDomain($pattern, $target);
    }

    private function dumpConfig(string $pattern, string $target): void
    {
        $file = sprintf('%s/%s',
            $target,
            strtr($pattern, [
                '{domain}' => 'config',
                '{_format}' => 'js'
            ])
        );

        $this->filesystem->mkdir(\dirname($file));

        if (file_exists($file)) {
            $this->filesystem->remove($file);
        }

        file_put_contents(
            $file,
            $this->twig->render('translations/config.js.twig', [
                'fallback' => $this->defaultLocale,
                'defaultDomain' => 'javascript',
            ])
        );
    }

    private function dumpTranslationsPerDomain($pattern, $target): void
    {
        foreach ($this->getTranslations() as $locale => $translations) {
            $renderContext = [
                'translations' => [$locale => ['javascript+intl-icu' => $translations]],
                'include_config' => false,
            ];

            $content = $this->twig->render('translations/translations.js.twig', $renderContext);

            $file = sprintf('%s/%s',
                $target,
                strtr($pattern, [
                    '{domain}' => sprintf('%s/%s', 'javascript+intl-icu', $locale),
                    '{_format}' => 'js'
                ])
            );

            $this->filesystem->mkdir(\dirname($file));

            if (file_exists($file)) {
                $this->filesystem->remove($file);
            }

            file_put_contents($file, $content);
        }
    }

    private function getTranslations(): array
    {
        $enabledLocales = implode(',', $this->enabledLocales);
        $finder = new Finder();
        $finder->files()->in($this->translationsPath)->name("javascript+intl-icu.{{$enabledLocales}}.xlf");

        $translations = [];

        foreach ($finder as $file) {
            $filename = $file->getPathname();
            [$extension, $locale, $domain] = explode('.', basename($filename), 3);

            if (!isset($translations[$locale])) {
                $translations[$locale] = [];
            }

            $loader = new XliffFileLoader();
            $catalogue = $loader->load($filename, $locale, $domain);

            $translations[$locale] = $catalogue->all($domain);
        }

        return $translations;
    }
}
