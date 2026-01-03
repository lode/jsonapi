<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
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
	])
	->withSkip([
		// better explicit readability
		IfIssetToCoalescingRector::class,
		// better explicit readability
		SimplifyUselessVariableRector::class,
		// explicit testing private properties
		RemoveUnusedPrivatePropertyRector::class => [
			'tests/ConverterTest.php',
		],
	])
	
	// tab-based indenting
	->withIndent(indentChar: "\t", indentSize: 1)
	
	// lowest supported php version
	->withPhpSets(php82: true)
	
	// slowly expand keys
	->withPreparedSets(
		deadCode: true,
		typeDeclarations: true,
	)
;
