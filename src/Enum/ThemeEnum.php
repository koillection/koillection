<?php

declare(strict_types=1);

namespace App\Enum;

class ThemeEnum
{
    public const THEME_TEAL = 'teal';
    public const THEME_CORAL = 'coral';

    public const THEMES = [
        self::THEME_TEAL,
        self::THEME_CORAL
    ];

    public const THEMES_TRANS_KEYS = [
        self::THEME_TEAL => 'teal',
        self::THEME_CORAL => 'coral'
    ];

    public static function getThemeLabels() : array
    {
        return [
            self::THEME_TEAL => 'global.themes.teal',
            self::THEME_CORAL => 'global.themes.coral'
        ];
    }
}
