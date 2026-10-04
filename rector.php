<?php

use Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets()
    ->withComposerBased(phpunit: true)
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
    ->withSkip([
        SimplifyUselessVariableRector::class,
        RemoveUnusedVariableAssignRector::class,
        SafeDeclareStrictTypesRector::class,
        RepeatedOrEqualToInArrayRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        SimplifyQuoteEscapeRector::class,
        RemoveAlwaysElseRector::class,
        NewlineBetweenClassLikeStmtsRector::class,
        NewlineBeforeNewAssignSetRector::class,
        __DIR__ . '/src/Model/*',
        __DIR__ . '/src/Table/Generated/*',
    ]);
