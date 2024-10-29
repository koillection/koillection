<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\CurrencyEnum;
use Symfony\Component\Intl\Countries;
use Twig\Extension\RuntimeExtensionInterface;

class IntlRuntime implements RuntimeExtensionInterface
{
    private readonly array $countries;

    public function __construct()
    {
        $this->countries = Countries::getNames();
    }

    public function getCurrenciesList(): array
    {
        $currencies = [];

        foreach (CurrencyEnum::getCurrencyLabels() as $currencyCode => $name) {
            $currencies[] = [
                'name' => $name,
                'code' => $currencyCode,
            ];
        }

        return $currencies;
    }

    public function getEmojiFlag(string $countryCode): string
    {
        $countryCode = mb_strtoupper($countryCode);
        if ($countryCode === 'EN') {
            $countryCode = 'US';
        }

        if (\strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, -2);
        }

        $regionalOffset = 0x1F1A5;

        return mb_chr($regionalOffset + mb_ord($countryCode[0], 'UTF-8'), 'UTF-8')
            . mb_chr($regionalOffset + mb_ord($countryCode[1], 'UTF-8'), 'UTF-8');
    }

    public function getCountriesList(): array
    {
        $countries = [];

        foreach ($this->countries as $countryCode => $name) {
            $countries[] = [
                'name' => $name,
                'code' => $countryCode,
                'flag' => $this->getEmojiFlag($countryCode),
            ];
        }

        return $countries;
    }

    public function getCountryName(string $code): string
    {
        return $this->countries[$code];
    }

    public function getCountryFlag(string $code): string
    {
        return $this->getEmojiFlag($code);
    }
}
