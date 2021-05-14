<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapiTests\TestableNonAbstractDocument as Document;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use alsvanzelf\jsonapiTests\profiles\TestProfile;
use PHPUnit\Framework\TestCase;

/**
 * @group SeparateProcess
 */
class SeparateProcessTest extends TestCase {
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_HappyPath() {
		$document = new Document();
		
		ob_start();
		$document->sendResponse();
		$output = ob_get_clean();
		
		$this->assertSame('{"jsonapi":{"version":"1.1"}}', $output);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_NoContent() {
		$document = new Document();
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
		if (extension_loaded('xdebug') === false) {
			$this->markTestSkipped('can not run without xdebug');
		}
		
		$document = new Document();
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL], xdebug_get_headers());
		
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
	 * @group Extensions
	 */
	public function testSendResponse_ContentTypeHeaderWithExtensions() {
		$extension = new TestExtension();
		$extension->setNamespace('one');
		$extension->setOfficialLink('https://jsonapi.org');
		
		$document = new Document();
		$document->applyExtension($extension);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL.'; ext="https://jsonapi.org"'], xdebug_get_headers());
		
		$extension = new TestExtension();
		$extension->setNamespace('two');
		$extension->setOfficialLink('https://jsonapi.org/2');
		$document->applyExtension($extension);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL.'; ext="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 * @group Profiles
	 */
	public function testSendResponse_ContentTypeHeaderWithProfiles() {
		if (extension_loaded('xdebug') === false) {
			$this->markTestSkipped('can not run without xdebug');
		}
		
		$profile = new TestProfile();
		$profile->setOfficialLink('https://jsonapi.org');
		
		$document = new Document();
		$document->applyProfile($profile);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL.'; profile="https://jsonapi.org"'], xdebug_get_headers());
		
		$profile = new TestProfile();
		$profile->setOfficialLink('https://jsonapi.org/2');
		$document->applyProfile($profile);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.Document::CONTENT_TYPE_OFFICIAL.'; profile="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_StatusCodeHeader() {
		$document = new Document();
		
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
		$document = new Document();
		$options  = ['json' => '{"foo":42}'];
		
		ob_start();
		$document->sendResponse($options);
		$output = ob_get_clean();
		
		$this->assertSame('{"foo":42}', $output);
	}
}
