<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
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
	
	public function testConstructor_NoContent(): void {
		$array = $this->document->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('jsonapi', $array);
	}
	
	public function testSetHttpStatusCode_HappyPath(): void {
		parent::assertTrue($this->document->hasHttpStatusCode());
		parent::assertSame(200, $this->document->getHttpStatusCode());
	}
	
	public function testAddLink_HappyPath(): void {
		$array = $this->document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$this->document->addLink('foo', 'https://jsonapi.org');
		
		$array = $this->document->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('foo', $array['links']);
		parent::assertIsString($array['links']['foo']);
		parent::assertSame('https://jsonapi.org', $array['links']['foo']);
	}
	
	public function testAddLink_WithMeta(): void {
		$this->document->addLink('foo', 'https://jsonapi.org', ['bar' => 'baz']);
		
		$array = $this->document->toArray();
		
		parent::assertCount(1, $array['links']);
		parent::assertIsArray($array['links']['foo']);
		parent::assertCount(2, $array['links']['foo']);
		parent::assertArrayHasKey('href', $array['links']['foo']);
		parent::assertArrayHasKey('meta', $array['links']['foo']);
		parent::assertSame('https://jsonapi.org', $array['links']['foo']['href']);
		parent::assertCount(1, $array['links']['foo']['meta']);
		parent::assertArrayHasKey('bar', $array['links']['foo']['meta']);
		parent::assertSame('baz', $array['links']['foo']['meta']['bar']);
	}
	
	public function testAddLink_BlocksJsonapiLevel(): void {
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "jsonapi" can not be used for links');
		
		$this->document->addLink('foo', 'https://jsonapi.org', level: DocumentLevelEnum::Jsonapi);
	}
	
	public function testAddLink_BlocksResourceLevel(): void {
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$this->document->addLink('foo', 'https://jsonapi.org', level: DocumentLevelEnum::Resource);
	}
	
	public function testSetSelfLink_HappyPath(): void {
		$array = $this->document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$this->document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $this->document->toArray();
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertSame('https://jsonapi.org/foo', $array['links']['self']);
	}
	
	public function testSetDescribedByLink_HappyPath(): void {
		$this->document->setDescribedByLink('https://jsonapi.org/format', ['version' => '1.1']);
		
		$array = $this->document->toArray();
		
		parent::assertCount(1, $array['links']);
		parent::assertIsArray($array['links']['describedby']);
		parent::assertCount(2, $array['links']['describedby']);
		parent::assertArrayHasKey('href', $array['links']['describedby']);
		parent::assertArrayHasKey('meta', $array['links']['describedby']);
		parent::assertSame('https://jsonapi.org/format', $array['links']['describedby']['href']);
		parent::assertCount(1, $array['links']['describedby']['meta']);
		parent::assertArrayHasKey('version', $array['links']['describedby']['meta']);
		parent::assertSame('1.1', $array['links']['describedby']['meta']['version']);
	}
	
	public function testSetDescribedByLink_WithMeta(): void {
		$array = $this->document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$this->document->setDescribedByLink('https://jsonapi.org/format');
		
		$array = $this->document->toArray();
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('describedby', $array['links']);
		parent::assertSame('https://jsonapi.org/format', $array['links']['describedby']);
	}
	
	public function testAddMeta_HappyPath(): void {
		$array = $this->document->toArray();
		parent::assertArrayNotHasKey('meta', $array);
		
		$this->document->addMeta('foo', 'bar');
		
		$array = $this->document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertIsString($array['meta']['foo']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAddMeta_AtJsonapiLevel(): void {
		$array = $this->document->toArray();
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayNotHasKey('meta', $array['jsonapi']);
		
		$this->document->addMeta('foo', 'bar', DocumentLevelEnum::Jsonapi);
		
		$array = $this->document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('meta', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['meta']);
		parent::assertArrayHasKey('foo', $array['jsonapi']['meta']);
		parent::assertIsString($array['jsonapi']['meta']['foo']);
		parent::assertSame('bar', $array['jsonapi']['meta']['foo']);
	}
	
	public function testAddMeta_BlocksResourceLevel(): void {
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$this->document->addMeta('foo', 'bar', DocumentLevelEnum::Resource);
	}
	
	public function testAddLinkObject_HappyPath(): void {
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$this->document->addLinkObject('foo', $linkObject);
		
		$array = $this->document->toArray();
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('foo', $array['links']);
		parent::assertArrayHasKey('href', $array['links']['foo']);
		parent::assertSame('https://jsonapi.org', $array['links']['foo']['href']);
	}
	
	/**
	 * @group Extensions
	 */
	public function testApplyExtension_HappyPath(): void {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, [
			'getNamespace'    => 'test',
			'getOfficialLink' => 'https://jsonapi.org/extension',
		]);
		
		$this->document->applyExtension($extension);
		$this->document->addExtensionMember($extension, 'foo', 'bar');
		$this->document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $this->document->toArray();
		
		parent::assertCount(3, $array);
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertCount(2, $array['jsonapi']);
		parent::assertSame('1.1', $array['jsonapi']['version']);
		parent::assertArrayHasKey('ext', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['ext']);
		parent::assertArrayHasKey(0, $array['jsonapi']['ext']);
		parent::assertSame('https://jsonapi.org/extension', $array['jsonapi']['ext'][0]);
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertSame('bar', $array['test:foo']);
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertCount(2, $array['links']['self']);
		parent::assertArrayHasKey('href', $array['links']['self']);
		parent::assertArrayHasKey('type', $array['links']['self']);
		parent::assertSame('https://jsonapi.org/foo', $array['links']['self']['href']);
		parent::assertSame('application/vnd.api+json; ext="https://jsonapi.org/extension"', $array['links']['self']['type']);
	}
	
	/**
	 * @group Extensions
	 */
	public function testApplyExtension_InvalidNamespace(): void {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'foo-bar']);
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('invalid namespace "foo-bar"');
		
		$this->document->applyExtension($extension);
	}
	
	/**
	 * @group Extensions
	 */
	public function testApplyExtension_ConflictingNamespace(): void {
		$extension1 = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'foo']);
		$this->document->applyExtension($extension1);
		
		$extension2 = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'bar']);
		$this->document->applyExtension($extension2);
		
		$extension3 = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'foo']);
		
		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('an extension with namespace "foo" is already applied');
		
		$this->document->applyExtension($extension3);
	}
	
	/**
	 * @group Profiles
	 */
	public function testApplyProfile_HappyPath(): void {
		$profile = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'https://jsonapi.org/profile']);
		
		$this->document->applyProfile($profile);
		$this->document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $this->document->toArray();
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertCount(2, $array['jsonapi']);
		parent::assertSame('1.1', $array['jsonapi']['version']);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertArrayHasKey(0, $array['jsonapi']['profile']);
		parent::assertSame('https://jsonapi.org/profile', $array['jsonapi']['profile'][0]);
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertCount(2, $array['links']['self']);
		parent::assertArrayHasKey('href', $array['links']['self']);
		parent::assertArrayHasKey('type', $array['links']['self']);
		parent::assertSame('https://jsonapi.org/foo', $array['links']['self']['href']);
		parent::assertSame('application/vnd.api+json; profile="https://jsonapi.org/profile"', $array['links']['self']['type']);
	}
	
	public function testToJson_HappyPath(): void {
		parent::assertSame('{"jsonapi":{"version":"1.1"}}', $this->document->toJson());
	}
	
	public function testToJson_CustomArray(): void {
		$options = ['array' => ['foo' => 42]];
		parent::assertSame('{"foo":42}', $this->document->toJson($options));
	}
	
	public function testToJson_PrettyPrint(): void {
		$options = ['prettyPrint' => true];
		$expectedJson = '{'.PHP_EOL.'    "jsonapi": {'.PHP_EOL.'        "version": "1.1"'.PHP_EOL.'    }'.PHP_EOL.'}';
		parent::assertSame($expectedJson, $this->document->toJson($options));
	}
	
	public function testToJson_JsonEncodeOptions(): void {
		$options = ['encodeOptions' => JSON_FORCE_OBJECT, 'array' => ['foo' => [4,2]]];
		parent::assertSame('{"foo":{"0":4,"1":2}}', $this->document->toJson($options));
	}
	
	public function testToJson_JsonpCallback(): void {
		$this->document->addMeta('foo', 'bar');
		
		$options = ['jsonpCallback' => 'baz'];
		$json    = $this->document->toJson($options);
		parent::assertSame('baz({"jsonapi":{"version":"1.1"},"meta":{"foo":"bar"}})', $json);
	}
	
	public function testToJson_InvalidUtf8(): void {
		$options = ['array' => ['foo' => "\xB1\x31"]];
		
		$this->expectException(\JsonException::class);
		$this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');
		$this->expectExceptionCode(JSON_ERROR_UTF8);
		
		$this->document->toJson($options);
	}
	
	public function testJsonSerialize_HappyPath(): void {
		$this->document->addMeta('foo', 'bar');
		
		$json = $this->document->toJson();
		
		parent::assertSame($json, json_encode($this->document, flags: JSON_THROW_ON_ERROR));
	}
}
