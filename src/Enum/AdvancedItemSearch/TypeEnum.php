<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

class TypeEnum
{
    public const string TYPE_DATUM = 'datum';

    public const array TYPES = [
        self::TYPE_DATUM
    ];

    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_DATUM => 'global.advanced_item_search.type.datum'
        ];
    }
}
