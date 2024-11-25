<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/api',
        __DIR__ . '/tests'
    ]);

    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

    $rectorConfig->rules([
        // PHP 7.1
        ListToArrayDestructRector::class,

        // PHP 7.4
        RestoreDefaultNullToNullableTypePropertyRector::class,
        NullCoalescingOperatorRector::class,

        // PHP 8.0
        ChangeSwitchToMatchRector::class,
        ClassOnObjectRector::class,
        StrContainsRector::class,
        StrEndsWithRector::class,
        StrStartsWithRector::class,
        StringableForToStringRector::class,

        // PHP 8.1
        NewInInitializerRector::class,
        ReadOnlyPropertyRector::class,

        // PHP 8.2
        ReadOnlyClassRector::class,

        // PHP 8.3
        AddOverrideAttributeToOverriddenMethodsRector::class,
        AddTypeToConstRector::class,

        // PHP 8.4
        ExplicitNullableParamTypeRector::class,

        //Type declaration
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,

        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,

        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,

        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ]);

    $rectorConfig->skip([
        EventListenerToEventSubscriberRector::class,
        AttributeKeyToClassConstFetchRector::class,
        ImproveDoctrineCollectionDocTypeInEntityRector::class,
        EncapsedStringsToSprintfRector::class,
        ExplicitBoolCompareRector::class,
        RenameClassRector::class,
        RenameClassConstFetchRector::class,
        SimplifyRegexPatternRector::class
    ]);
};
