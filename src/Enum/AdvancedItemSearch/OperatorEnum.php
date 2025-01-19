<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

class OperatorEnum
{
    public const string OPERATOR_EQUAL = 'equal';

    public const array OPERATORS = [
        self::OPERATOR_EQUAL,
    ];

    public static function getOperatorLabels(): array
    {
        return [
            self::OPERATOR_EQUAL => 'global.advanced_item_search.operator.equal'
        ];
    }
}
