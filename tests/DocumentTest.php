<?php

namespace alsvanzelf\jsonapiTests;

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
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('jsonapi', $array);
	}
	
	public function testSetHttpStatusCode_HappyPath() {
		$document = new Document();
		
		$this->assertTrue($document->hasHttpStatusCode());
		$this->assertSame(200, $document->getHttpStatusCode());
	}
	
	public function testAddLink_HappyPath() {
		$document = new Document();
		
		$array = $document->toArray();
		$this->assertArrayNotHasKey('links', $array);
		
		$document->addLink('foo', 'https://jsonapi.org');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(1, $array['links']);
		$this->assertArrayHasKey('foo', $array['links']);
		if (method_exists($this, 'assertIsString')) {
			$this->assertIsString($array['links']['foo']);
		}
		else {
			$this->assertInternalType('string', $array['links']['foo']);
		}
		$this->assertSame('https://jsonapi.org', $array['links']['foo']);
	}
	
	public function testAddLink_WithMeta() {
		$document = new Document();
		$document->addLink('foo', 'https://jsonapi.org', $meta=['bar' => 'baz']);
		
		$array = $document->toArray();
		
		$this->assertCount(1, $array['links']);
		if (method_exists($this, 'assertIsArray')) {
			$this->assertIsArray($array['links']['foo']);
		}
		else {
			$this->assertInternalType('array', $array['links']['foo']);
		}
		$this->assertCount(2, $array['links']['foo']);
		$this->assertArrayHasKey('href', $array['links']['foo']);
		$this->assertArrayHasKey('meta', $array['links']['foo']);
		$this->assertSame('https://jsonapi.org', $array['links']['foo']['href']);
		$this->assertCount(1, $array['links']['foo']['meta']);
		$this->assertArrayHasKey('bar', $array['links']['foo']['meta']);
		$this->assertSame('baz', $array['links']['foo']['meta']['bar']);
	}
	
	public function testAddLink_BlocksJsonapiLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "jsonapi" can not be used for links');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=Document::LEVEL_JSONAPI);
	}
	
	public function testAddLink_BlocksResourceLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=Document::LEVEL_RESOURCE);
	}
	
	public function testAddLink_BlocksUnknownLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('unknown level "foo"');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level='foo');
	}
	
	public function testAddMeta_HappyPath() {
		$document = new Document();
		
		$array = $document->toArray();
		$this->assertArrayNotHasKey('meta', $array);
		
		$document->addMeta('foo', 'bar');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertCount(1, $array['meta']);
		$this->assertArrayHasKey('foo', $array['meta']);
		if (method_exists($this, 'assertIsString')) {
			$this->assertIsString($array['meta']['foo']);
		}
		else {
			$this->assertInternalType('string', $array['meta']['foo']);
		}
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAddMeta_AtJsonapiLevel() {
		$document = new Document();
		
		$array = $document->toArray();
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayNotHasKey('meta', $array['jsonapi']);
		
		$document->addMeta('foo', 'bar', $level=Document::LEVEL_JSONAPI);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('meta', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['meta']);
		$this->assertArrayHasKey('foo', $array['jsonapi']['meta']);
		if (method_exists($this, 'assertIsString')) {
			$this->assertIsString($array['jsonapi']['meta']['foo']);
		}
		else {
			$this->assertInternalType('string', $array['jsonapi']['meta']['foo']);
		}
		$this->assertSame('bar', $array['jsonapi']['meta']['foo']);
	}
	
	public function testAddMeta_BlocksResourceLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addMeta('foo', 'bar', $level=Document::LEVEL_RESOURCE);
	}
	
	public function testAddMeta_BlocksUnknownLevel() {
		$document = new Document();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('unknown level "foo"');
		
		$document->addMeta('foo', 'bar', $level='foo');
	}
	
	public function testAddLinkObject_HappyPath() {
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$document = new Document();
		$document->addLinkObject($key='foo', $linkObject);
		
		$array = $document->toArray();
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('foo', $array['links']);
		$this->assertArrayHasKey('href', $array['links']['foo']);
		$this->assertSame('https://jsonapi.org', $array['links']['foo']['href']);
	}
	
	/**
	 * @group Extensions
	 */
	public function testApplyExtension_HappyPath() {
		$extension = new TestExtension();
		$extension->setNamespace('test');
		$extension->setOfficialLink('https://jsonapi.org');
		
		$document = new Document();
		$document->applyExtension($extension);
		$document->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertCount(2, $array['jsonapi']);
		$this->assertSame('1.1', $array['jsonapi']['version']);
		$this->assertArrayHasKey('ext', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['ext']);
		$this->assertArrayHasKey(0, $array['jsonapi']['ext']);
		$this->assertSame('https://jsonapi.org', $array['jsonapi']['ext'][0]);
		$this->assertArrayHasKey('test:foo', $array);
		$this->assertSame('bar', $array['test:foo']);
	}
	
	public function testApplyExtension_InvalidNamespace() {
		$document  = new Document();
		$extension = new TestExtension();
		$extension->setNamespace('foo-bar');
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('invalid namespace "foo-bar"');
		
		$document->applyExtension($extension);
	}
	
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
		$profile->setOfficialLink('https://jsonapi.org');
		
		$document = new Document();
		$document->applyProfile($profile);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertCount(2, $array['jsonapi']);
		$this->assertSame('1.1', $array['jsonapi']['version']);
		$this->assertArrayHasKey('profile', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['profile']);
		$this->assertArrayHasKey(0, $array['jsonapi']['profile']);
		$this->assertSame('https://jsonapi.org', $array['jsonapi']['profile'][0]);
	}
	
	public function testToJson_HappyPath() {
		$document = new Document();
		
		$this->assertSame('{"jsonapi":{"version":"1.1"}}', $document->toJson());
	}
	
	public function testToJson_CustomArray() {
		$document = new Document();
		
		$options = ['array' => ['foo' => 42]];
		$this->assertSame('{"foo":42}', $document->toJson($options));
	}
	
	public function testToJson_PrettyPrint() {
		$document = new Document();
		
		$options = ['prettyPrint' => true];
		$expectedJson = '{'.PHP_EOL.'    "jsonapi": {'.PHP_EOL.'        "version": "1.1"'.PHP_EOL.'    }'.PHP_EOL.'}';
		$this->assertSame($expectedJson, $document->toJson($options));
	}
	
	public function testToJson_JsonEncodeOptions() {
		$document = new Document();
		
		$options = ['encodeOptions' => JSON_FORCE_OBJECT, 'array' => ['foo' => [4,2]]];
		$this->assertSame('{"foo":{"0":4,"1":2}}', $document->toJson($options));
	}
	
	public function testToJson_JsonpCallback() {
		$document = new Document();
		$document->addMeta('foo', 'bar');
		
		$options = ['jsonpCallback' => 'baz'];
		$json    = $document->toJson($options);
		$this->assertSame('baz({"jsonapi":{"version":"1.1"},"meta":{"foo":"bar"}})', $json);
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
		
		$this->assertSame($json, json_encode($document));
	}
}
