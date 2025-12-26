<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapiTests\TestableNonAbstractDocument;
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
		$document = new TestableNonAbstractDocument();
		
		ob_start();
		$document->sendResponse();
		$output = ob_get_clean();
		
		$this->assertSame('{"jsonapi":{"version":"1.1"}}', $output);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_NoContent() {
		$document = new TestableNonAbstractDocument();
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
		
		$document = new TestableNonAbstractDocument();
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Official];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Debug];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Debug->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Jsonp];
		ob_start();
		$document->sendResponse($options);
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Jsonp->value], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 * @group Extensions
	 */
	public function testSendResponse_ContentTypeHeaderWithExtensions() {
		if (extension_loaded('xdebug') === false) {
			$this->markTestSkipped('can not run without xdebug');
		}
		
		$extension = new TestExtension();
		$extension->setNamespace('one');
		$extension->setOfficialLink('https://jsonapi.org');
		
		$document = new TestableNonAbstractDocument();
		$document->applyExtension($extension);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; ext="https://jsonapi.org"'], xdebug_get_headers());
		
		$extension = new TestExtension();
		$extension->setNamespace('two');
		$extension->setOfficialLink('https://jsonapi.org/2');
		$document->applyExtension($extension);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; ext="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
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
		
		$document = new TestableNonAbstractDocument();
		$document->applyProfile($profile);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; profile="https://jsonapi.org"'], xdebug_get_headers());
		
		$profile = new TestProfile();
		$profile->setOfficialLink('https://jsonapi.org/2');
		$document->applyProfile($profile);
		
		ob_start();
		$document->sendResponse();
		ob_end_clean();
		$this->assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; profile="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_StatusCodeHeader() {
		$document = new TestableNonAbstractDocument();
		
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
		$document = new TestableNonAbstractDocument();
		$options  = ['json' => '{"foo":42}'];
		
		ob_start();
		$document->sendResponse($options);
		$output = ob_get_clean();
		
		$this->assertSame('{"foo":42}', $output);
	}
}
