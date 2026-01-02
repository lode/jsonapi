<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

// @see https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md for more rules

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
		__DIR__ . '/examples',
	])
	->withRules([
		DeclareStrictTypesRector::class,
		AddTestsVoidReturnTypeWhereNoReturnRector::class,
		AddVoidReturnTypeWhereNoReturnRector::class,
	])
	->withSkip([
		// better explicit readability
		IfIssetToCoalescingRector::class,
	])
	
	// tab-based indenting
	->withIndent(indentChar: "\t", indentSize: 1)
	
	// lowest supported php version
	->withPhpSets(php82: true)
	
	// slowly increase levels
	->withTypeCoverageLevel(1)
	->withDeadCodeLevel(1)
	
	// @todo add `->withPreparedSets()` once on a higher level with other rules
;
