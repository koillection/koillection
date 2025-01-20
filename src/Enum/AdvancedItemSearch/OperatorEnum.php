<?php

declare(strict_types=1);

namespace App\Enum\AdvancedItemSearch;

use App\Enum\DatumTypeEnum;

class OperatorEnum
{
    public const string OPERATOR_EQUAL = 'equal';

    public const string OPERATOR_CONTAINS = 'contains';

    public const string OPERATOR_SUPERIOR = 'superior';

    public const string OPERATOR_SUPERIOR_OR_EQUAL = 'superior-or-equal';

    public const string OPERATOR_INFERIOR = 'inferior';

    public const string OPERATOR_INFERIOR_OR_EQUAL = 'inferior-or-equal';

    public static function getOperatorLabels(): array
    {
        return [
            self::OPERATOR_EQUAL => 'global.advanced_item_search.operator.equal',
            self::OPERATOR_CONTAINS => 'global.advanced_item_search.operator.contains',
            self::OPERATOR_SUPERIOR => 'global.advanced_item_search.operator.superior',
            self::OPERATOR_SUPERIOR_OR_EQUAL => 'global.advanced_item_search.operator.superior_or_equal',
            self::OPERATOR_INFERIOR => 'global.advanced_item_search.operator.inferior',
            self::OPERATOR_INFERIOR_OR_EQUAL => 'global.advanced_item_search.operator.inferior_or_equal',
        ];
    }

    public static function getLabelFromName(string $name): string
    {
        return self::getOperatorLabels()[$name];
    }

    public static function getOperatorsByType(string $type): array
    {
        return match ($type) {
            DatumTypeEnum::TYPE_TEXT, DatumTypeEnum::TYPE_TEXTAREA, DatumTypeEnum::TYPE_LINK, 'item_name' => [
                OperatorEnum::OPERATOR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_EQUAL),
                OperatorEnum::OPERATOR_CONTAINS => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_CONTAINS)
            ],
            DatumTypeEnum::TYPE_COUNTRY, DatumTypeEnum::TYPE_CHECKBOX => [
                OperatorEnum::OPERATOR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_EQUAL)
            ],
            DatumTypeEnum::TYPE_DATE, DatumTypeEnum::TYPE_NUMBER, DatumTypeEnum::TYPE_RATING => [
                OperatorEnum::OPERATOR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_EQUAL),
                OperatorEnum::OPERATOR_SUPERIOR => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_SUPERIOR),
                OperatorEnum::OPERATOR_SUPERIOR_OR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_SUPERIOR_OR_EQUAL),
                OperatorEnum::OPERATOR_INFERIOR => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_INFERIOR),
                OperatorEnum::OPERATOR_INFERIOR_OR_EQUAL => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_INFERIOR_OR_EQUAL),
            ],
            DatumTypeEnum::TYPE_LIST, DatumTypeEnum::TYPE_CHOICE_LIST => [
                OperatorEnum::OPERATOR_CONTAINS => OperatorEnum::getLabelFromName(OperatorEnum::OPERATOR_CONTAINS)
            ],
        };
    }
}
