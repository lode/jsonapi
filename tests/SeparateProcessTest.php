<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use PHPUnit\Framework\TestCase;

/**
 * @group SeparateProcess
 */
class SeparateProcessTest extends TestCase {
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_HappyPath() {
		$document = new DataDocument();
		
		ob_start();
		$document->sendResponse();
		$output = ob_get_clean();
		
		$this->assertSame('{"jsonapi":{"version":"1.0"},"data":null}', $output);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_NoContent() {
		$document = new DataDocument();
		$document->setHttpStatusCode(204);
		
		ob_start();
		$document->sendResponse();
		$output = ob_get_clean();
		
		$this->assertSame('', $output);
		$this->assertSame(204, http_response_code());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_ContentTypeHeader() {
		$document = new DataDocument();
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_DEFAULT], xdebug_get_headers());
		
		$options = ['contentType' => Document::CONTENT_TYPE_OFFICIAL];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL], xdebug_get_headers());
		
		$options = ['contentType' => Document::CONTENT_TYPE_DEBUG];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_DEBUG], xdebug_get_headers());
		
		$options = ['contentType' => Document::CONTENT_TYPE_JSONP];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_JSONP], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_StatusCodeHeader() {
		$document = new DataDocument();
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(200, http_response_code());
		
		$document->setHttpStatusCode(201);
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(201, http_response_code());
		
		$document->setHttpStatusCode(422);
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(422, http_response_code());
		
		$document->setHttpStatusCode(503);
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(503, http_response_code());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_CustomJson() {
		$document = new DataDocument();
		$options  = ['json' => '{"foo":42}'];
		
		ob_start();
		$document->sendResponse($options);
		$output = ob_get_clean();
		
		$this->assertSame('{"foo":42}', $output);
	}
}
