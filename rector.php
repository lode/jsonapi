<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

// @see https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md for more rules

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
		__DIR__ . '/examples',
	])
	->withRootFiles()
	->withRules([
		DeclareStrictTypesRector::class,
	])
	->withSkip([
		// better explicit readability
		FlipTypeControlToUseExclusiveTypeRector::class,
		IfIssetToCoalescingRector::class,
		SimplifyUselessVariableRector::class,
		SimplifyIfReturnBoolRector::class,
		
		// not all rules from code style
		NewlineAfterStatementRector::class,
		NewlineBeforeNewAssignSetRector::class,
		NewlineBetweenClassLikeStmtsRector::class,
		SimplifyQuoteEscapeRector::class,
		
		// rely on types from interfaces
		DocblockGetterReturnArrayFromPropertyDocblockVarRector::class,
		
		// better readability using function declaration sorting
		SortCallLikeNamedArgsRector::class,
		
		// explicit testing private properties
		RemoveUnusedPrivatePropertyRector::class => [
			'tests/ConverterTest.php',
		],
	])
	
	// tab-based indenting
	->withIndent(indentChar: "\t", indentSize: 1)
	// importing FQNs
	->withImportNames(importShortClasses: false)
	
	// lowest supported php version
	->withPhpSets(php82: true)
	
	// expand with new keys
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		codingStyle: true,
		typeDeclarations: true,
		typeDeclarationDocblocks: true,
		privatization: true,
		phpunitCodeQuality: true,
		
		// prefer own style
		// naming: true,
		// instanceOf: true,
		// earlyReturn: true,
		// rectorPreset: true,
		
		// not used
		// carbon: true,
		// doctrineCodeQuality: true,
		// symfonyCodeQuality: true,
		// symfonyConfigs: true,
	)
	
	// vendor sets
	->withAttributesSets()
	->withComposerBased(phpunit: true)
;
