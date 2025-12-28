<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\HttpStatusCodeManager;
use PHPUnit\Framework\TestCase;

class HttpStatusCodeManagerTest extends TestCase {
	private static object $helper;
	
	public static function setUpBeforeClass(): void {
		// using HttpStatusCodeManager to make it non-trait to test against it
		self::$helper = new class {
			use HttpStatusCodeManager;
		};
	}
	
	public function testSetHttpStatusCode_HappyPath(): void {
		parent::assertFalse(self::$helper->hasHttpStatusCode());
		
		self::$helper->setHttpStatusCode(204);
		
		parent::assertTrue(self::$helper->hasHttpStatusCode());
		parent::assertSame(204, self::$helper->getHttpStatusCode());
	}
	
	public function testSetHttpStatusCode_InvalidForHttp(): void {
		$this->expectException(InputException::class);
		
		self::$helper->setHttpStatusCode(42);
	}
	
	public function testSetHttpStatusCode_AllowsYetUnknownHttpCodes(): void {
		self::$helper->setHttpStatusCode(299);
		
		parent::assertTrue(self::$helper->hasHttpStatusCode());
		parent::assertSame(299, self::$helper->getHttpStatusCode());
	}
}
