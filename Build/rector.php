<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Set\ValueObject\SetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // php rules
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_53);
    $containerConfigurator->import(SetList::PHP_54);
    $containerConfigurator->import(SetList::PHP_55);
    $containerConfigurator->import(SetList::PHP_56);
    $containerConfigurator->import(SetList::PHP_70);
    $containerConfigurator->import(SetList::PHP_71);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::PHP_74);

    // typo3 rules
    $containerConfigurator->import(Typo3SetList::TYPO3_76);
    $containerConfigurator->import(Typo3SetList::TCA_76);
    $containerConfigurator->import(Typo3SetList::TYPO3_87);
    $containerConfigurator->import(Typo3SetList::TCA_87);
    $containerConfigurator->import(Typo3SetList::TYPO3_95);
    $containerConfigurator->import(Typo3SetList::TCA_95);
    $containerConfigurator->import(Typo3SetList::TYPO3_104);
    $containerConfigurator->import(Typo3SetList::TYPO3_104);
    $containerConfigurator->import(Typo3SetList::TCA_104);
    $containerConfigurator->import(Typo3SetList::UNDERSCORE_TO_NAMESPACE);
    $containerConfigurator->import(Typo3SetList::DATABASE_TO_DBAL);

    // is your PHP version different from the one your refactor to? [default: your PHP version]
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // auto import fully qualified class names? [default: false]
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // is there a file you need to skip?
    $parameters->set(Option::SKIP, [
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        AddLiteralSeparatorToNumberRector::class,
        \Rector\Php71\Rector\FuncCall\CountOnNullRector::class,
        \Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector::class,
        NameImportingPostRector::class => [
            dirname(__DIR__) . '/Build/*',
            dirname(__DIR__) . '/.Build/*',
            'ClassAliasMap.php',
            'ext_localconf.php',
            'ext_emconf.php',
            'ext_tables.php',
            dirname(__DIR__) . '/Configuration/TCA/*',
            dirname(__DIR__) . '/Configuration/RequestMiddlewares.php',
            dirname(__DIR__) . '/Configuration/Commands.php',
            dirname(__DIR__) . '/Configuration/AjaxRoutes.php',
            dirname(__DIR__) . '/Configuration/Extbase/Persistence/Classes.php',
        ],
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
//        __DIR__ . '/typo3.constants.php',
    ]);

    /*// get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(ContentObjectRendererFileResourceRector::class);
    $services->set(TemplateGetFileNameToFilePathSanitizerRector::class);*/

};
