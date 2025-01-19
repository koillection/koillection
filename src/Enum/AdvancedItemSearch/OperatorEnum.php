<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

class OperatorEnum
{
    public const string OPERATOR_EQUAL = 'equal';

    public const string OPERATOR_CONTAINS = 'contains';

    public const array OPERATORS = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_CONTAINS,
    ];

    public static function getOperatorLabels(): array
    {
        return [
            self::OPERATOR_EQUAL => 'global.advanced_item_search.operator.equal',
            self::OPERATOR_CONTAINS => 'global.advanced_item_search.operator.contains'
        ];
    }

    public static function getLabelFromName(string $name): string
    {
        return self::getOperatorLabels()[$name];
    }
}
