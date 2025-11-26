<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// @see https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md for more rules

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
		__DIR__ . '/examples',
	])
	->withSkip([
		__DIR__ . '/src/base.php',
		__DIR__ . '/src/collection.php',
		__DIR__ . '/src/error.php',
		__DIR__ . '/src/errors.php',
		__DIR__ . '/src/exception.php',
		__DIR__ . '/src/resource.php',
		__DIR__ . '/src/response.php',
	])

	// tab-based indenting
	->withIndent(indentChar: "\t", indentSize: 1)

	// slowly increase php version
	->withPhpSets(php56: true)

	// slowly increase levels
	->withTypeCoverageLevel(1)
	->withDeadCodeLevel(1)

	// @todo add `->withPreparedSets()` once on a higher level with other rules
;
