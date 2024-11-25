<?php

declare(strict_types=1);

namespace App\Service;

use Composer\Semver\Comparator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LatestReleaseChecker
{
    public const array REQUIRED_PHP_VERSION_PER_RELEASE = [
        '1.0' => '7.2',
        '1.1' => '7.4',
        '1.2' => '8.0',
        '1.3' => '8.1',
        '1.4' => '8.2',
        '1.5' => '8.3',
        '1.6' => '8.4',
    ];

    private ?array $latestReleaseData = null;

    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire(param: 'release')] private readonly string $currentRelease
    ) {
    }

    public function getLatestReleaseData(): ?array
    {
        if ($this->latestReleaseData !== null && $this->latestReleaseData !== []) {
            return $this->latestReleaseData;
        }

        try {
            $response = $this->client->request(
                'GET',
                'https://api.github.com/repos/koillection/koillection/tags',
                ['timeout' => 2.5, 'verify_peer' => false, 'verify_host' => false]
            );
            if (200 !== $response->getStatusCode()) {
                throw new \Exception();
            }

            $content = json_decode($response->getContent(), true);
            $this->latestReleaseData = $content[0];
        } catch (\Exception $exception) {
        }

        return $this->latestReleaseData;
    }

    public function getCurrentRelease(): ?string
    {
        return $this->currentRelease;
    }

    public function getLatestRelease(): ?string
    {
        $latestReleaseData = $this->getLatestReleaseData();
        if ($latestReleaseData === null || $latestReleaseData === []) {
            return null;
        }

        return $latestReleaseData['name'];
    }

    public function getRequiredPhpVersionForLatestRelease(): ?string
    {
        $latestRelease = $this->getLatestRelease();

        if (null === $latestRelease) {
            return null;
        }

        preg_match('/\d+.\d+/', $latestRelease, $output);
        $latestMinor = $output[0];

        foreach (self::REQUIRED_PHP_VERSION_PER_RELEASE as $koillectionRelease => $phpVersion) {
            if (Comparator::equalTo($koillectionRelease, $latestMinor)) {
                return $phpVersion;
            }
        }

        return null;
    }

    public function isRequiredPhpVersionForLatestReleaseOk(): bool|int
    {
        return Comparator::greaterThanOrEqualTo(phpversion(), $this->getRequiredPhpVersionForLatestRelease());
    }
}
