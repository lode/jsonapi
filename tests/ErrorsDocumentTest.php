<?php

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ErrorObject;

class ErrorsDocumentTest extends TestCase {
	public function testFromException_HappyPath() {
		$document = ErrorsDocument::fromException(new \Exception('foo', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertArrayHasKey('meta', $array['errors'][0]);
		$this->assertArrayHasKey('type', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('message', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('code', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('trace', $array['errors'][0]['meta']);
		$this->assertSame('Exception', $array['errors'][0]['code']);
		$this->assertSame('Exception', $array['errors'][0]['meta']['type']);
		$this->assertSame('foo', $array['errors'][0]['meta']['message']);
		$this->assertSame(42, $array['errors'][0]['meta']['code']);
		$this->assertArrayHasKey('function', $array['errors'][0]['meta']['trace'][0]);
		$this->assertArrayHasKey('class', $array['errors'][0]['meta']['trace'][0]);
		$this->assertSame(__FUNCTION__, $array['errors'][0]['meta']['trace'][0]['function']);
		$this->assertSame(__CLASS__, $array['errors'][0]['meta']['trace'][0]['class']);
	}
	
	/**
	 * @group non-php5
	 */
	public function testFromException_AllowsThrowable() {
		if (PHP_MAJOR_VERSION < 7) {
			$this->markTestSkipped('can not run in php5');
			return;
		}
		
		$document = ErrorsDocument::fromException(new \Error('foo', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertArrayHasKey('meta', $array['errors'][0]);
		$this->assertArrayHasKey('type', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('message', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('code', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('trace', $array['errors'][0]['meta']);
		$this->assertSame('Error', $array['errors'][0]['code']);
		$this->assertSame('Error', $array['errors'][0]['meta']['type']);
		$this->assertSame('foo', $array['errors'][0]['meta']['message']);
		$this->assertSame(42, $array['errors'][0]['meta']['code']);
		$this->assertArrayHasKey('function', $array['errors'][0]['meta']['trace'][0]);
		$this->assertArrayHasKey('class', $array['errors'][0]['meta']['trace'][0]);
		$this->assertSame(__FUNCTION__, $array['errors'][0]['meta']['trace'][0]['function']);
		$this->assertSame(__CLASS__, $array['errors'][0]['meta']['trace'][0]['class']);
	}
	
	public function testFromException_BlocksNonException() {
		$this->expectException(InputException::class);
		
		ErrorsDocument::fromException(new \stdClass());
	}
	
	public function testAddException_WithPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		
		$document = new ErrorsDocument();
		$document->addException($exception);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(2, $array['errors']);
		$this->assertArrayHasKey('meta', $array['errors'][0]);
		$this->assertArrayHasKey('meta', $array['errors'][1]);
		$this->assertArrayHasKey('message', $array['errors'][0]['meta']);
		$this->assertArrayHasKey('message', $array['errors'][1]['meta']);
		$this->assertSame('foo', $array['errors'][0]['meta']['message']);
		$this->assertSame('bar', $array['errors'][1]['meta']['message']);
	}
	
	public function testAddException_SkipPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		$options   = ['includeExceptionPrevious' => false];
		
		$document = new ErrorsDocument();
		$document->addException($exception, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('meta', $array['errors'][0]);
		$this->assertArrayHasKey('message', $array['errors'][0]['meta']);
		$this->assertSame('foo', $array['errors'][0]['meta']['message']);
	}
	
	public function testAddException_BlocksNonException() {
		$document = new ErrorsDocument();
		
		$this->expectException(InputException::class);
		
		$document->addException(new \stdClass());
	}
	
	public function testToArray_EmptyErrorObject() {
		$document = new ErrorsDocument();
		$document->addErrorObject(new ErrorObject('foo'));
		$document->addErrorObject(new ErrorObject());
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertSame('foo', $array['errors'][0]['code']);
	}
	
	/**
	 * @param non-empty-array<int> $allErrorCodes
	 */
	#[DataProvider('dataProviderDetermineHttpStatusCode_HappyPath')]
	public function testDetermineHttpStatusCode_HappyPath(int $expectedAdvisedErrorCode, array $allErrorCodes) {
		$document = new ErrorsDocument();
		
		$method = new \ReflectionMethod($document, 'determineHttpStatusCode');
		$method->setAccessible(true);
		
		$advisedErrorCode = null;
		foreach ($allErrorCodes as $errorCode) {
			$advisedErrorCode = $method->invoke($document, $errorCode);
		}
		
		$this->assertSame($expectedAdvisedErrorCode, $advisedErrorCode);
	}
	
	public static function dataProviderDetermineHttpStatusCode_HappyPath() {
		return [
			[422, [422]],
			[422, [422, 422]],
			[400, [422, 404]],
			[400, [400]],
			[501, [501]],
			[501, [501, 501]],
			[500, [501, 503]],
			[500, [422, 404, 501, 503]],
			[500, [500]],
			[302, [302]],
		];
	}
	
	public function testDetermineHttpStatusCode_Override() {
		$document = new ErrorsDocument();
		
		$this->assertSame(200, $document->getHttpStatusCode());
		
		$allErrorCodes = [422, 404, 501, 503];
		foreach ($allErrorCodes as $errorCode) {
			$errorObject = new ErrorObject();
			$errorObject->setHttpStatusCode($errorCode);
			
			$document->addErrorObject($errorObject);
		}
		
		$this->assertSame(500, $document->getHttpStatusCode());
		
		$document->setHttpStatusCode(422);
		
		$this->assertSame(422, $document->getHttpStatusCode());
	}
}
