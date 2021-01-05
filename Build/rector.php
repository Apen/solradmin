<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // here we can define, what sets of rules will be applied
    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PHP_53,
        SetList::PHP_54,
        SetList::PHP_55,
        SetList::PHP_56,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
    ]);

    //$containerConfigurator->import(__DIR__ . '/../../../../../vendor/ssch/typo3-rector/config/database-connection-to-dbal.php');
    $containerConfigurator->import(__DIR__ . '/../.Build/vendor/ssch/typo3-rector/config/services.php');
    $containerConfigurator->import(__DIR__ . '/../.Build/vendor/ssch/typo3-rector/config/typo3-8.7.php');
    $containerConfigurator->import(__DIR__ . '/../.Build/vendor/ssch/typo3-rector/config/typo3-9.5.php');
    $containerConfigurator->import(__DIR__ . '/../.Build/vendor/ssch/typo3-rector/config/typo3-10.4.php');

    // is your PHP version different from the one your refactor to? [default: your PHP version]
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');

    // auto import fully qualified class names? [default: false]
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // is there a file you need to skip?
    $parameters->set(Option::EXCLUDE_PATHS, [
        // single file
        realpath(__DIR__ . '/../') . '/ext_emconf.php',
        realpath(__DIR__ . '/../') . '/ext_localconf.php',
        realpath(__DIR__ . '/../') . '/ext_tables.php',
        realpath(__DIR__ . '/../') . '/Configuration/*',
        realpath(__DIR__ . '/../') . '/Build/*',
        realpath(__DIR__ . '/../') . '/.Build/*',
    ]);

    // is there single rule you don't like from a set you use?
    $parameters->set(Option::EXCLUDE_RECTORS, [
        \Rector\Php71\Rector\FuncCall\CountOnNullRector::class,
        \Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector::class
    ]);
};
