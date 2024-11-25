<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\ConfigurationEnum;
use App\Model\ConfigurationAdmin;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class ConfigurationHelper
{
    private const string PREFIX = 'configuration_';

    private readonly ApcuAdapter $cache;

    public function __construct(private readonly ConfigurationRepository $configurationRepository)
    {
        $this->cache = new ApcuAdapter();
    }

    public function getAdminConfiguration(): ConfigurationAdmin
    {
        $thumbnailsFormat = $this->configurationRepository->findOneBy(['label' => ConfigurationEnum::THUMBNAILS_FORMAT]);
        $customCssLightTheme = $this->configurationRepository->findOneBy(['label' => ConfigurationEnum::CUSTOM_LIGHT_THEME_CSS]);
        $customCssDarkTheme = $this->configurationRepository->findOneBy(['label' => ConfigurationEnum::CUSTOM_DARK_THEME_CSS]);
        $enableMetrics = $this->configurationRepository->findOneBy(['label' => ConfigurationEnum::ENABLE_METRICS]);

        return new ConfigurationAdmin($thumbnailsFormat, $customCssLightTheme, $customCssDarkTheme, $enableMetrics);
    }

    public function getValue(string $label): ?string
    {
        $configurationRepository = $this->configurationRepository;

        return $this->cache->get(self::PREFIX . $label, static function (ItemInterface $item) use ($configurationRepository, $label) {
            $configuration = $configurationRepository->findOneBy(['label' => $label]);

            return $configuration?->getValue();
        });
    }

    public function clearCache(): void
    {
        $this->cache->clear(self::PREFIX);
    }
}
