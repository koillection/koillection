<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IntlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrenciesList', static function (): array {
                return (new IntlRuntime())->getCurrenciesList();
            }),
            new TwigFunction('getCountriesList', static function (): array {
                return (new IntlRuntime())->getCountriesList();
            }),
            new TwigFunction('getCountryName', static function (string $code): string {
                return (new IntlRuntime())->getCountryName($code);
            }),
            new TwigFunction('getCountryFlag', static function (string $code): string {
                return (new IntlRuntime())->getCountryFlag($code);
            }),
        ];
    }
}
