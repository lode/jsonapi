<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase {
	/**
	 * cleanup once phpunit has `expectExceptionMessageIs()` (php ^8.4 and phpunit ^13.3)
	 */
    protected function expectExceptionMessageIs_(string $message): void
    {
        if (method_exists($this, 'expectExceptionMessageIs') === false) {
        	parent::expectExceptionMessageMatches('/^'.preg_quote($message, delimiter: '/').'$/');
        }
        else {
        	parent::expectExceptionMessageIs($message);
        }
    }
}
