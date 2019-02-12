<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ErrorObject;
use PHPUnit\Framework\TestCase;

class ErrorObjectTest extends TestCase {
	public function testFromException_HappyPath() {
		$exception   = new \Exception('foo', 1);
		$errorObject = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertSame('1', $array['code']);
	}
	
	public function testFromException_ExposeAll() {
		$exception    = new \Exception('foo', 1);
		$expectedLine = (__LINE__ - 1);
		$options      = ['exceptionExposeDetails' => true];
		$errorObject  = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		$this->assertCount(3, $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('title', $array);
		$this->assertArrayHasKey('meta', $array);
		$this->assertSame('1', $array['code']);
		$this->assertSame('Exception', $array['title']);
		$this->assertCount(4, $array['meta']);
		$this->assertArrayHasKey('message', $array['meta']);
		$this->assertArrayHasKey('file', $array['meta']);
		$this->assertArrayHasKey('line', $array['meta']);
		$this->assertArrayHasKey('trace', $array['meta']);
		$this->assertSame('foo', $array['meta']['message']);
		$this->assertSame(__FILE__, $array['meta']['file']);
		$this->assertSame($expectedLine, $array['meta']['line']);
		$this->assertGreaterThan(1, $array['meta']['trace']);
		$this->assertArrayHasKey('function', $array['meta']['trace'][0]);
		$this->assertSame(__FUNCTION__, $array['meta']['trace'][0]['function']);
		$this->assertSame(__CLASS__, $array['meta']['trace'][0]['class']);
	}
	
	public function testFromException_DoNotExposeTrace() {
		$exception    = new \Exception('foo', 1);
		$expectedLine = (__LINE__ - 1);
		$options      = ['exceptionExposeDetails' => true, 'exceptionExposeTrace' => false];
		$errorObject  = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertSame('1', $array['code']);
		$this->assertSame('Exception', $array['title']);
		$this->assertCount(3, $array['meta']);
		$this->assertArrayHasKey('message', $array['meta']);
		$this->assertArrayHasKey('file', $array['meta']);
		$this->assertArrayHasKey('line', $array['meta']);
		$this->assertArrayNotHasKey('trace', $array['meta']);
	}
	
	public function testFromException_BlocksNonException() {
		$this->expectException(InputException::class);
		
		ErrorObject::fromException(new \stdClass());
	}
	
	public function testIsEmpty_All() {
		$errorObject = new ErrorObject();
		$this->assertTrue($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setUniqueIdentifier(42);
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHttpStatusCode(422);
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setApplicationCode(42);
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHumanTitle('foo');
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHumanDetails('foo');
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addLink('foo', 'https://jsonapi.org');
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addSource('parameter', 'bar');
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addMeta('foo', 'bar');
		$this->assertFalse($errorObject->isEmpty());
	}
}
