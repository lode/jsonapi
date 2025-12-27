<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group SeparateProcess
 */
class SeparateProcessTest extends TestCase {
	private object $document;
	
	public function setUp(): void {
		/**
		 * extending Document to make it non-abstract to test against it
		 * 
		 * the abstract declaration is to make sure to create valid jsonapi output
		 * as it needs at least one of `data`, `meta` or `errors`
		 */
		$this->document = new class extends Document {};
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_HappyPath() {
		ob_start();
		$this->document->sendResponse();
		$output = ob_get_clean();
		
		parent::assertSame('{"jsonapi":{"version":"1.1"}}', $output);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_NoContent() {
		$this->document->setHttpStatusCode(204);
		
		ob_start();
		$this->document->sendResponse();
		$output = ob_get_clean();
		
		parent::assertSame('', $output);
		parent::assertSame(204, http_response_code());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_ContentTypeHeader() {
		if (extension_loaded('xdebug') === false) {
			parent::markTestSkipped('can not run without xdebug');
		}
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Official];
		ob_start();
		$this->document->sendResponse($options);
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Debug];
		ob_start();
		$this->document->sendResponse($options);
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Debug->value], xdebug_get_headers());
		
		$options = ['contentType' => ContentTypeEnum::Jsonp];
		ob_start();
		$this->document->sendResponse($options);
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Jsonp->value], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 * @group Extensions
	 */
	public function testSendResponse_ContentTypeHeaderWithExtensions() {
		if (extension_loaded('xdebug') === false) {
			parent::markTestSkipped('can not run without xdebug');
		}
		
		$extension = parent::createConfiguredStub(ExtensionInterface::class, [
			'getNamespace'    => 'one',
			'getOfficialLink' => 'https://jsonapi.org',
		]);
		$this->document->applyExtension($extension);
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; ext="https://jsonapi.org"'], xdebug_get_headers());
		
		$extension = parent::createConfiguredStub(ExtensionInterface::class, [
			'getNamespace'    => 'two',
			'getOfficialLink' => 'https://jsonapi.org/2',
		]);
		$this->document->applyExtension($extension);
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; ext="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 * @group Profiles
	 */
	public function testSendResponse_ContentTypeHeaderWithProfiles() {
		if (extension_loaded('xdebug') === false) {
			parent::markTestSkipped('can not run without xdebug');
		}
		
		$profile = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'https://jsonapi.org']);
		$this->document->applyProfile($profile);
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; profile="https://jsonapi.org"'], xdebug_get_headers());
		
		$profile = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'https://jsonapi.org/2']);
		$this->document->applyProfile($profile);
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(['Content-Type: '.ContentTypeEnum::Official->value.'; profile="https://jsonapi.org https://jsonapi.org/2"'], xdebug_get_headers());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_StatusCodeHeader() {
		
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(200, http_response_code());
		
		$this->document->setHttpStatusCode(201);
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(201, http_response_code());
		
		$this->document->setHttpStatusCode(422);
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(422, http_response_code());
		
		$this->document->setHttpStatusCode(503);
		ob_start();
		$this->document->sendResponse();
		ob_end_clean();
		parent::assertSame(503, http_response_code());
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testSendResponse_CustomJson() {
		$options  = ['json' => '{"foo":42}'];
		
		ob_start();
		$this->document->sendResponse($options);
		$output = ob_get_clean();
		
		parent::assertSame('{"foo":42}', $output);
	}
}
