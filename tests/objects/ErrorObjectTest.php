<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ErrorObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class ErrorObjectTest extends TestCase {
	public function testFromException_HappyPath() {
		$exception    = new \Exception('foo', 1);
		$expectedLine = (__LINE__ - 1);
		$errorObject  = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('meta', $array);
		$this->assertSame('Exception', $array['code']);
		$this->assertCount(6, $array['meta']);
		$this->assertArrayHasKey('type', $array['meta']);
		$this->assertArrayHasKey('message', $array['meta']);
		$this->assertArrayHasKey('code', $array['meta']);
		$this->assertArrayHasKey('file', $array['meta']);
		$this->assertArrayHasKey('line', $array['meta']);
		$this->assertArrayHasKey('trace', $array['meta']);
		$this->assertSame('Exception', $array['meta']['type']);
		$this->assertSame('foo', $array['meta']['message']);
		$this->assertSame(1, $array['meta']['code']);
		$this->assertSame(__FILE__, $array['meta']['file']);
		$this->assertSame($expectedLine, $array['meta']['line']);
		$this->assertGreaterThan(1, $array['meta']['trace']);
		$this->assertArrayHasKey('function', $array['meta']['trace'][0]);
		$this->assertArrayHasKey('class', $array['meta']['trace'][0]);
		$this->assertSame(__FUNCTION__, $array['meta']['trace'][0]['function']);
		$this->assertSame(__CLASS__, $array['meta']['trace'][0]['class']);
	}
	
	public function testFromException_DoNotExposeTrace() {
		$exception    = new \Exception('foo', 1);
		$expectedLine = (__LINE__ - 1);
		$options      = ['includeExceptionTrace' => false];
		$errorObject  = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertSame('Exception', $array['code']);
		$this->assertCount(5, $array['meta']);
		$this->assertArrayHasKey('type', $array['meta']);
		$this->assertArrayHasKey('message', $array['meta']);
		$this->assertArrayHasKey('code', $array['meta']);
		$this->assertArrayHasKey('file', $array['meta']);
		$this->assertArrayHasKey('line', $array['meta']);
		$this->assertArrayNotHasKey('trace', $array['meta']);
	}
	
	public function testFromException_StripFilePath() {
		$exception   = new \Exception('foo', 1);
		$basePath    = realpath(__DIR__.'/../../').'/';
		$options     = ['stripExceptionBasePath' => $basePath];
		$errorObject = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('file', $array['meta']);
		$this->assertArrayHasKey('trace', $array['meta']);
		$this->assertSame('tests/objects/ErrorObjectTest.php', $array['meta']['file']);
		$this->assertGreaterThan(2, $array['meta']['trace']);
		$this->assertArrayHasKey('file', $array['meta']['trace'][1]);
		$this->assertSame('vendor/phpunit/phpunit/src/Framework/TestCase.php', $array['meta']['trace'][1]['file']);
	}
	
	public function testFromException_NamespacedException() {
		$exception   = new InputException();
		$errorObject = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('type', $array['meta']);
		$this->assertSame('Input Exception', $array['code']);
		$this->assertSame('alsvanzelf\jsonapi\exceptions\InputException', $array['meta']['type']);
	}
	
	/**
	 * @group non-php5
	 */
	public function testFromException_NamespacedThrowable() {
		if (PHP_MAJOR_VERSION < 7) {
			$this->markTestSkipped('can not run in php5');
			return;
		}
		
		$exception   = new TestError();
		$errorObject = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('type', $array['meta']);
		$this->assertSame('Test Error', $array['code']);
		$this->assertSame('alsvanzelf\jsonapiTests\objects\TestError', $array['meta']['type']);
	}
	
	public function testFromException_BlocksNonException() {
		$this->expectException(InputException::class);
		
		ErrorObject::fromException(new \stdClass());
	}
	
	/**
	 * @deprecated array links are not supported anymore
	 */
	public function testAppendTypeLink_HappyPath() {
		$errorObject = new ErrorObject();
		$this->assertTrue($errorObject->isEmpty());
		
		$errorObject->appendTypeLink('https://jsonapi.org');
		
		$this->assertFalse($errorObject->isEmpty());
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('type', $array['links']);
		$this->assertSame(['https://jsonapi.org'], $array['links']['type']);
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
		
		$errorObject = new ErrorObject();
		$errorObject->addAtMember('context', 'test');
		$this->assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		$this->assertFalse($errorObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testToArray_WithExtensionMembers() {
		$errorObject = new ErrorObject();
		$extension   = new TestExtension();
		$extension->setNamespace('test');
		
		$this->assertSame([], $errorObject->toArray());
		
		$errorObject->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('test:foo', $array);
		$this->assertSame('bar', $array['test:foo']);
	}
}

if (PHP_MAJOR_VERSION >= 7) {
	class TestError extends \Error {}
}
