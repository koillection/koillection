<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

class TypeEnum
{
    public const string TYPE_NAME = 'name';
    public const string TYPE_DATUM = 'datum';

    public const array TYPES = [
        self::TYPE_DATUM,
        self::TYPE_NAME,
    ];

    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_NAME => 'global.advanced_item_search.type.name',
            self::TYPE_DATUM => 'global.advanced_item_search.type.datum'
        ];
    }
}
