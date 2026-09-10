<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        __DIR__.'/rector.php',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkipPath(__DIR__.'/vendor')
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        phpunitCodeQuality: true
    )
    ->withSkip([
        // assertNull on a ?Object return states the contract; assertNotInstanceOf obscures it.
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        ThrowWithPreviousExceptionRector::class,
        DisallowedEmptyRuleFixerRector::class,
    ]);
