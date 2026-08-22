<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\Runner\Version;

class TestCase extends PHPUnitTestCase {
	/**
	 * cleanup once phpunit has `expectExceptionMessageIs()` (php ^8.4 and phpunit ^13.3)
	 */
	protected function expectExceptionMessageIs_(string $message): void {
		if (version_compare(Version::id(), '13.3', '<')) {
			parent::expectExceptionMessageMatches('/^'.preg_quote($message, delimiter: '/').'$/');
		}
		else {
			parent::expectExceptionMessageIs($message);
		}
	}
}
