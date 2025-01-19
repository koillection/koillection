<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

class ConditionEnum
{
    public const string CONDITION_AND = 'and';

    public const string CONDITION_OR = 'or';

    public const array CONDITIONS = [
        self::CONDITION_AND,
        self::CONDITION_OR,
    ];

    public static function getConditionLabels(): array
    {
        return [
            self::CONDITION_AND => 'global.advanced_item_search.condition.and',
            self::CONDITION_OR => 'global.advanced_item_search.condition.or',
        ];
    }
}
