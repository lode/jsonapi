<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\ErrorsDocument;
use PHPUnit\Framework\TestCase;

class ErrorsDocumentTest extends TestCase {
	public function testAddException_WithPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		
		$document = new ErrorsDocument();
		$document->addException($exception);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(2, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertArrayHasKey('code', $array['errors'][1]);
		$this->assertSame('1', $array['errors'][0]['code']);
		$this->assertSame('2', $array['errors'][1]['code']);
	}
	
	public function testAddException_SkipPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		$options   = ['exceptionSkipPrevious' => true];
		
		$document = new ErrorsDocument();
		$document->addException($exception, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertSame('1', $array['errors'][0]['code']);
	}
	
	/**
	 * @dataProvider dataProviderDetermineHttpStatusCode_HappyPath
	 */
	public function testDetermineHttpStatusCode_HappyPath($expectedAdvisedErrorCode, $allErrorCodes) {
		$document = new ErrorsDocument();
		
		$method = new \ReflectionMethod($document, 'determineHttpStatusCode');
		$method->setAccessible(true);
		
		foreach ($allErrorCodes as $errorCode) {
			$advisedErrorCode = $method->invoke($document, $errorCode);
		}
		
		$this->assertSame($expectedAdvisedErrorCode, $advisedErrorCode);
	}
	
	public function dataProviderDetermineHttpStatusCode_HappyPath() {
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
}
