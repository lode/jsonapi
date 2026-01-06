<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

final class ErrorsDocumentTest extends TestCase {
	public function testFromException_HappyPath(): void {
		$document = ErrorsDocument::fromException(new \Exception('foo', 42));
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('errors', $array);
		parent::assertCount(1, $array['errors']);
		parent::assertArrayHasKey('code', $array['errors'][0]);
		parent::assertArrayHasKey('meta', $array['errors'][0]);
		parent::assertArrayHasKey('type', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('message', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('code', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('trace', $array['errors'][0]['meta']);
		parent::assertSame('Exception', $array['errors'][0]['code']);
		parent::assertSame('Exception', $array['errors'][0]['meta']['type']);
		parent::assertSame('foo', $array['errors'][0]['meta']['message']);
		parent::assertSame(42, $array['errors'][0]['meta']['code']);
		parent::assertArrayHasKey('function', $array['errors'][0]['meta']['trace'][0]);
		parent::assertArrayHasKey('class', $array['errors'][0]['meta']['trace'][0]);
		parent::assertSame(__FUNCTION__, $array['errors'][0]['meta']['trace'][0]['function']);
		parent::assertSame(self::class, $array['errors'][0]['meta']['trace'][0]['class']);
	}
	
	public function testFromException_AllowsThrowable(): void {
		$document = ErrorsDocument::fromException(new \Error('foo', 42));
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('errors', $array);
		parent::assertCount(1, $array['errors']);
		parent::assertArrayHasKey('code', $array['errors'][0]);
		parent::assertArrayHasKey('meta', $array['errors'][0]);
		parent::assertArrayHasKey('type', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('message', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('code', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('trace', $array['errors'][0]['meta']);
		parent::assertSame('Error', $array['errors'][0]['code']);
		parent::assertSame('Error', $array['errors'][0]['meta']['type']);
		parent::assertSame('foo', $array['errors'][0]['meta']['message']);
		parent::assertSame(42, $array['errors'][0]['meta']['code']);
		parent::assertArrayHasKey('function', $array['errors'][0]['meta']['trace'][0]);
		parent::assertArrayHasKey('class', $array['errors'][0]['meta']['trace'][0]);
		parent::assertSame(__FUNCTION__, $array['errors'][0]['meta']['trace'][0]['function']);
		parent::assertSame(self::class, $array['errors'][0]['meta']['trace'][0]['class']);
	}
	
	public function testAddException_WithPrevious(): void {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		
		$document = new ErrorsDocument();
		$document->addException($exception);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('errors', $array);
		parent::assertCount(2, $array['errors']);
		parent::assertArrayHasKey('meta', $array['errors'][0]);
		parent::assertArrayHasKey('meta', $array['errors'][1]);
		parent::assertArrayHasKey('message', $array['errors'][0]['meta']);
		parent::assertArrayHasKey('message', $array['errors'][1]['meta']);
		parent::assertSame('foo', $array['errors'][0]['meta']['message']);
		parent::assertSame('bar', $array['errors'][1]['meta']['message']);
	}
	
	public function testAddException_SkipPrevious(): void {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		$options   = ['includeExceptionPrevious' => false];
		
		$document = new ErrorsDocument();
		$document->addException($exception, $options);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('errors', $array);
		parent::assertCount(1, $array['errors']);
		parent::assertArrayHasKey('meta', $array['errors'][0]);
		parent::assertArrayHasKey('message', $array['errors'][0]['meta']);
		parent::assertSame('foo', $array['errors'][0]['meta']['message']);
	}
	
	public function testToArray_EmptyErrorObject(): void {
		$document = new ErrorsDocument();
		$document->addErrorObject(new ErrorObject('foo'));
		$document->addErrorObject(new ErrorObject());
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('errors', $array);
		parent::assertCount(1, $array['errors']);
		parent::assertArrayHasKey('code', $array['errors'][0]);
		parent::assertSame('foo', $array['errors'][0]['code']);
	}
	
	/**
	 * @param non-empty-array<int> $allErrorCodes
	 */
	#[DataProvider('dataProviderDetermineHttpStatusCode_HappyPath')]
	public function testDetermineHttpStatusCode_HappyPath(int $expectedAdvisedErrorCode, array $allErrorCodes): void {
		$document = new ErrorsDocument();
		
		$method = new \ReflectionMethod($document, 'determineHttpStatusCode');
		
		$advisedErrorCode = null;
		foreach ($allErrorCodes as $errorCode) {
			$advisedErrorCode = $method->invoke($document, $errorCode);
		}
		
		parent::assertSame($expectedAdvisedErrorCode, $advisedErrorCode);
	}
	
	/**
	 * @return \Iterator<(int | string), array{int, non-empty-array<int>}>
	 */
	public static function dataProviderDetermineHttpStatusCode_HappyPath(): \Iterator {
		yield [422, [422]];
		yield [422, [422, 422]];
		yield [400, [422, 404]];
		yield [400, [400]];
		yield [501, [501]];
		yield [501, [501, 501]];
		yield [500, [501, 503]];
		yield [500, [422, 404, 501, 503]];
		yield [500, [500]];
		yield [302, [302]];
	}
	
	public function testDetermineHttpStatusCode_Override(): void {
		$document = new ErrorsDocument();
		
		parent::assertSame(200, $document->getHttpStatusCode());
		
		$allErrorCodes = [422, 404, 501, 503];
		foreach ($allErrorCodes as $errorCode) {
			$errorObject = new ErrorObject();
			$errorObject->setHttpStatusCode($errorCode);
			
			$document->addErrorObject($errorObject);
		}
		
		parent::assertSame(500, $document->getHttpStatusCode());
		
		$document->setHttpStatusCode(422);
		
		parent::assertSame(422, $document->getHttpStatusCode());
	}
}
