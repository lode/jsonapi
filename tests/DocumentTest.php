<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapiTests\TestableNonAbstractDocument as Document;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use alsvanzelf\jsonapiTests\profiles\TestProfile;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
	public function testConstructor_NoContent() {
		$document = new Document();
		
		$array = $document->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('jsonapi', $array);
	}
	
	public function testSetHttpStatusCode_HappyPath() {
		$document = new Document();
		
		parent::assertTrue($document->hasHttpStatusCode());
		parent::assertSame(200, $document->getHttpStatusCode());
	}
	
	public function testAddLink_HappyPath() {
		$document = new Document();
		
		$array = $document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$document->addLink('foo', 'https://jsonapi.org');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('foo', $array['links']);
		parent::assertIsString($array['links']['foo']);
		parent::assertSame('https://jsonapi.org', $array['links']['foo']);
	}
	
	public function testAddLink_WithMeta() {
		$document = new Document();
		$document->addLink('foo', 'https://jsonapi.org', $meta=['bar' => 'baz']);
		
		$array = $document->toArray();
		
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
	
	public function testAddLink_BlocksJsonapiLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "jsonapi" can not be used for links');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=DocumentLevelEnum::Jsonapi);
	}
	
	public function testAddLink_BlocksResourceLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=DocumentLevelEnum::Resource);
	}
	
	public function testSetSelfLink_HappyPath() {
		$document = new Document();
		
		$array = $document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $document->toArray();
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertSame('https://jsonapi.org/foo', $array['links']['self']);
	}
	
	public function testSetDescribedByLink_HappyPath() {
		$document = new Document();
		$document->setDescribedByLink('https://jsonapi.org/format', ['version' => '1.1']);
		
		$array = $document->toArray();
		
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
	
	public function testSetDescribedByLink_WithMeta() {
		$document = new Document();
		
		$array = $document->toArray();
		parent::assertArrayNotHasKey('links', $array);
		
		$document->setDescribedByLink('https://jsonapi.org/format');
		
		$array = $document->toArray();
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('describedby', $array['links']);
		parent::assertSame('https://jsonapi.org/format', $array['links']['describedby']);
	}
	
	public function testAddMeta_HappyPath() {
		$document = new Document();
		
		$array = $document->toArray();
		parent::assertArrayNotHasKey('meta', $array);
		
		$document->addMeta('foo', 'bar');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertIsString($array['meta']['foo']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAddMeta_AtJsonapiLevel() {
		$document = new Document();
		
		$array = $document->toArray();
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayNotHasKey('meta', $array['jsonapi']);
		
		$document->addMeta('foo', 'bar', $level=DocumentLevelEnum::Jsonapi);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('meta', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['meta']);
		parent::assertArrayHasKey('foo', $array['jsonapi']['meta']);
		parent::assertIsString($array['jsonapi']['meta']['foo']);
		parent::assertSame('bar', $array['jsonapi']['meta']['foo']);
	}
	
	public function testAddMeta_BlocksResourceLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addMeta('foo', 'bar', $level=DocumentLevelEnum::Resource);
	}
	
	public function testAddLinkObject_HappyPath() {
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$document = new Document();
		$document->addLinkObject($key='foo', $linkObject);
		
		$array = $document->toArray();
		
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
	public function testApplyExtension_HappyPath() {
		$extension = new TestExtension();
		$extension->setNamespace('test');
		$extension->setOfficialLink('https://jsonapi.org/extension');
		
		$document = new Document();
		$document->applyExtension($extension);
		$document->addExtensionMember($extension, 'foo', 'bar');
		$document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $document->toArray();
		
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
	public function testApplyExtension_InvalidNamespace() {
		$document  = new Document();
		$extension = new TestExtension();
		$extension->setNamespace('foo-bar');
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('invalid namespace "foo-bar"');
		
		$document->applyExtension($extension);
	}
	
	/**
	 * @group Extensions
	 */
	public function testApplyExtension_ConflictingNamespace() {
		$document  = new Document();
		
		$extension1 = new TestExtension();
		$extension1->setNamespace('foo');
		$document->applyExtension($extension1);
		
		$extension2 = new TestExtension();
		$extension2->setNamespace('bar');
		$document->applyExtension($extension2);
		
		$extension3 = new TestExtension();
		$extension3->setNamespace('foo');
		
		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('an extension with namespace "foo" is already applied');
		
		$document->applyExtension($extension3);
	}
	
	/**
	 * @group Profiles
	 */
	public function testApplyProfile_HappyPath() {
		$profile = new TestProfile();
		$profile->setOfficialLink('https://jsonapi.org/profile');
		
		$document = new Document();
		$document->applyProfile($profile);
		$document->setSelfLink('https://jsonapi.org/foo');
		
		$array = $document->toArray();
		
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
	
	public function testToJson_HappyPath() {
		$document = new Document();
		
		parent::assertSame('{"jsonapi":{"version":"1.1"}}', $document->toJson());
	}
	
	public function testToJson_CustomArray() {
		$document = new Document();
		
		$options = ['array' => ['foo' => 42]];
		parent::assertSame('{"foo":42}', $document->toJson($options));
	}
	
	public function testToJson_PrettyPrint() {
		$document = new Document();
		
		$options = ['prettyPrint' => true];
		$expectedJson = '{'.PHP_EOL.'    "jsonapi": {'.PHP_EOL.'        "version": "1.1"'.PHP_EOL.'    }'.PHP_EOL.'}';
		parent::assertSame($expectedJson, $document->toJson($options));
	}
	
	public function testToJson_JsonEncodeOptions() {
		$document = new Document();
		
		$options = ['encodeOptions' => JSON_FORCE_OBJECT, 'array' => ['foo' => [4,2]]];
		parent::assertSame('{"foo":{"0":4,"1":2}}', $document->toJson($options));
	}
	
	public function testToJson_JsonpCallback() {
		$document = new Document();
		$document->addMeta('foo', 'bar');
		
		$options = ['jsonpCallback' => 'baz'];
		$json    = $document->toJson($options);
		parent::assertSame('baz({"jsonapi":{"version":"1.1"},"meta":{"foo":"bar"}})', $json);
	}
	
	public function testToJson_InvalidUtf8() {
		$document = new Document();
		
		$options = ['array' => ['foo' => "\xB1\x31"]];
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('failed to generate json: Malformed UTF-8 characters, possibly incorrectly encoded');
		
		$document->toJson($options);
	}
	
	public function testJsonSerialize_HappyPath() {
		$document = new Document();
		$document->addMeta('foo', 'bar');
		
		$json = $document->toJson();
		
		parent::assertSame($json, json_encode($document));
	}
}
