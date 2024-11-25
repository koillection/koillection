<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FooterExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderFooter', static function (Environment $environment, $object): string {
                return (new FooterRuntime())->renderFooter($environment, $object);
            }, ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
}
