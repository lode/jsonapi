<?php

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ErrorObject;
use PHPUnit\Framework\TestCase;

class ErrorObjectTest extends TestCase {
	public function testSetHttpStatusCode_HappyPath() {
		$errorObject = new ErrorObject();
		
		$this->assertNull($errorObject->status);
		
		$errorObject->setHttpStatusCode(204);
		
		$this->assertSame('204', $errorObject->status);
	}
	
	public function testSetHttpStatusCode_InvalidForHttp() {
		$errorObject = new ErrorObject();
		
		$this->expectException(InputException::class);
		
		$errorObject->setHttpStatusCode(42);
	}
	
	public function testSetHttpStatusCode_AllowsYetUnknownHttpCodes() {
		$errorObject = new ErrorObject();
		
		$errorObject->setHttpStatusCode(299);
		
		$this->assertSame('299', $errorObject->status);
	}
}
