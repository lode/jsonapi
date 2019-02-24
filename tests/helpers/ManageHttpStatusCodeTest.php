<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitManageHttpStatusCode as ManageHttpStatusCode;
use PHPUnit\Framework\TestCase;

class ManageHttpStatusCodeTest extends TestCase {
	public function testSetHttpStatusCode_HappyPath() {
		$helper = new ManageHttpStatusCode();
		
		$this->assertFalse($helper->hasHttpStatusCode());
		$this->assertNull($helper->getHttpStatusCode());
		
		$helper->setHttpStatusCode(204);
		
		$this->assertTrue($helper->hasHttpStatusCode());
		$this->assertSame(204, $helper->getHttpStatusCode());
	}
	
	public function testSetHttpStatusCode_InvalidForHttp() {
		$helper = new ManageHttpStatusCode();
		
		$this->expectException(InputException::class);
		
		$helper->setHttpStatusCode(42);
	}
	
	public function testSetHttpStatusCode_AllowsYetUnknownHttpCodes() {
		$helper = new ManageHttpStatusCode();
		
		$helper->setHttpStatusCode(299);
		
		$this->assertTrue($helper->hasHttpStatusCode());
		$this->assertSame(299, $helper->getHttpStatusCode());
	}
}
