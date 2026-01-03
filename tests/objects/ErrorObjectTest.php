<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\objects\ErrorObject;
use PHPUnit\Framework\TestCase;

final class ErrorObjectTest extends TestCase {
	public function testFromException_HappyPath(): void {
		$exception    = new \Exception('foo', 1);
		$expectedLine = (__LINE__ - 1);
		$errorObject  = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('meta', $array);
		parent::assertSame('Exception', $array['code']);
		parent::assertCount(6, $array['meta']);
		parent::assertArrayHasKey('type', $array['meta']);
		parent::assertArrayHasKey('message', $array['meta']);
		parent::assertArrayHasKey('code', $array['meta']);
		parent::assertArrayHasKey('file', $array['meta']);
		parent::assertArrayHasKey('line', $array['meta']);
		parent::assertArrayHasKey('trace', $array['meta']);
		parent::assertSame('Exception', $array['meta']['type']);
		parent::assertSame('foo', $array['meta']['message']);
		parent::assertSame(1, $array['meta']['code']);
		parent::assertSame(__FILE__, $array['meta']['file']);
		parent::assertSame($expectedLine, $array['meta']['line']);
		parent::assertGreaterThan(1, $array['meta']['trace']);
		parent::assertArrayHasKey('function', $array['meta']['trace'][0]);
		parent::assertArrayHasKey('class', $array['meta']['trace'][0]);
		parent::assertSame(__FUNCTION__, $array['meta']['trace'][0]['function']);
		parent::assertSame(self::class, $array['meta']['trace'][0]['class']);
	}
	
	public function testFromException_DoNotExposeTrace(): void {
		$exception    = new \Exception('foo', 1);
		$options      = ['includeExceptionTrace' => false];
		$errorObject  = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertSame('Exception', $array['code']);
		parent::assertCount(5, $array['meta']);
		parent::assertArrayHasKey('type', $array['meta']);
		parent::assertArrayHasKey('message', $array['meta']);
		parent::assertArrayHasKey('code', $array['meta']);
		parent::assertArrayHasKey('file', $array['meta']);
		parent::assertArrayHasKey('line', $array['meta']);
		parent::assertArrayNotHasKey('trace', $array['meta']);
	}
	
	public function testFromException_StripFilePath(): void {
		$exception   = new \Exception('foo', 1);
		$basePath    = realpath(__DIR__.'/../../').'/';
		$options     = ['stripExceptionBasePath' => $basePath];
		$errorObject = ErrorObject::fromException($exception, $options);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('file', $array['meta']);
		parent::assertArrayHasKey('trace', $array['meta']);
		parent::assertSame('tests/objects/ErrorObjectTest.php', $array['meta']['file']);
		parent::assertGreaterThan(2, $array['meta']['trace']);
		parent::assertArrayHasKey('file', $array['meta']['trace'][1]);
		parent::assertSame('vendor/phpunit/phpunit/src/Framework/TestCase.php', $array['meta']['trace'][1]['file']);
	}
	
	public function testFromException_NamespacedException(): void {
		$exception   = new InputException();
		$errorObject = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('type', $array['meta']);
		parent::assertSame('Input Exception', $array['code']);
		parent::assertSame('alsvanzelf\jsonapi\exceptions\InputException', $array['meta']['type']);
	}
	
	public function testFromException_NamespacedThrowable(): void {
		$exception   = new TestError();
		$errorObject = ErrorObject::fromException($exception);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('type', $array['meta']);
		parent::assertSame('Test Error', $array['code']);
		parent::assertSame('alsvanzelf\jsonapiTests\objects\TestError', $array['meta']['type']);
	}
	
	public function testIsEmpty_All(): void {
		$errorObject = new ErrorObject();
		parent::assertTrue($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setUniqueIdentifier(42);
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHttpStatusCode(422);
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setApplicationCode(42);
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHumanTitle('foo');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->setHumanDetails('foo');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addLink('foo', 'https://jsonapi.org');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addSource('pointer', '/bar');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addSource('parameter', 'bar');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addSource('header', 'X-Bar');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addMeta('foo', 'bar');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addAtMember('context', 'test');
		parent::assertFalse($errorObject->isEmpty());
		
		$errorObject = new ErrorObject();
		$errorObject->addExtensionMember(parent::createStub(ExtensionInterface::class), 'foo', 'bar');
		parent::assertFalse($errorObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testToArray_WithExtensionMembers(): void {
		$errorObject = new ErrorObject();
		$extension   = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']);
		
		parent::assertSame([], $errorObject->toArray());
		
		$errorObject->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertSame('bar', $array['test:foo']);
	}
}

class TestError extends \Error {}
