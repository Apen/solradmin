<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrailingCommaInMultilineArrayFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use \PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use \PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PHP_CS_FIXER);

    $parameters->set(Option::SKIP, [
        dirname(__DIR__) . '/Build/*',
        TrailingCommaInMultilineFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class,
        PhpUnitStrictFixer::class,
        PhpUnitTestAnnotationFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        ArrayListItemNewlineFixer::class,
        CastSpacesFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        ClassAttributesSeparationFixer::class,
        OrderedClassElementsFixer::class,
        NoSpacesAroundOffsetFixer::class,
        AssignmentInConditionSniff::class . '.FoundInWhileCondition',
        DeclareStrictTypesFixer::class => [
            dirname(__DIR__) . '/ext_emconf.php',
            dirname(__DIR__) . '/ext_localconf.php',
            dirname(__DIR__) . '/ext_tables.php',
        ]
    ]);
    $services = $containerConfigurator->services();

    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [['syntax' => 'short']]);

    $services->set(ConcatSpaceFixer::class)
        ->call('configure', [['spacing' => 'one']]);

    $services->set(BinaryOperatorSpacesFixer::class)
        ->call('configure', [['default' => 'single_space']]);

    $services->set(NoExtraBlankLinesFixer::class);

    $services->set(TernaryOperatorSpacesFixer::class);

    $services->set(NoBlankLinesAfterPhpdocFixer::class);

    $services->set(AlignMultilineCommentFixer::class)
        ->call('configure', [['comment_type' => 'phpdocs_only']]);

    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [['annotations' => ['author', 'since']]]);

    $services->set(NoLeadingImportSlashFixer::class);

    $services->set(NoUnusedImportsFixer::class);

    $services->set(OrderedImportsFixer::class)
        ->call('configure', [['imports_order' => ['class', 'const', 'function']]]);

    $services->set(CyclomaticComplexitySniff::class)
        ->property('complexity', 20)
        ->property('absoluteComplexity', 20);
};
