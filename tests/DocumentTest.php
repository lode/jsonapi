<?php

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
	public function testSetHttpStatusCode_HappyPath() {
		$document = new DataDocument();
		
		$this->assertSame(200, $document->httpStatusCode);
		
		$document->setHttpStatusCode(204);
		
		$this->assertSame(204, $document->httpStatusCode);
	}
	
	public function testSetHttpStatusCode_InvalidForHttp() {
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		
		$document->setHttpStatusCode(42);
	}
	
	public function testSetHttpStatusCode_AllowsYetUnknownHttpCodes() {
		$document = new DataDocument();
		
		$document->setHttpStatusCode(299);
		
		$this->assertSame(299, $document->httpStatusCode);
	}
	
	public function testAddLink_HappyPath() {
		$document = new DataDocument();
		
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
		$document = new DataDocument();
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
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "jsonapi" can not be used for links');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=Document::LEVEL_JSONAPI);
	}
	
	public function testAddLink_BlocksResourceLevel() {
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level=Document::LEVEL_RESOURCE);
	}
	
	public function testAddLink_BlocksUnknownLevel() {
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('unknown level "foo"');
		
		$document->addLink('foo', 'https://jsonapi.org', $meta=[], $level='foo');
	}
	
	public function testAddMeta_HappyPath() {
		$document = new DataDocument();
		
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
		$document = new DataDocument();
		
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
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('level "resource" can only be set on a ResourceDocument');
		
		$document->addMeta('foo', 'bar', $level=Document::LEVEL_RESOURCE);
	}
	
	public function testAddMeta_BlocksUnknownLevel() {
		$document = new DataDocument();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('unknown level "foo"');
		
		$document->addMeta('foo', 'bar', $level='foo');
	}
	
	public function testToJson_HappyPath() {
		$document = new DataDocument();
		
		$this->assertSame('{"jsonapi":{"version":"1.0"},"data":null}', $document->toJson());
	}
	
	public function testToJson_CustomArray() {
		$document = new DataDocument();
		
		$options = ['array' => ['foo' => 42]];
		$this->assertSame('{"foo":42}', $document->toJson($options));
	}
	
	public function testToJson_InvalidUtf8() {
		$document = new DataDocument();
		
		$options = ['array' => ['foo' => "\xB1\x31"]];
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('failed to generate json: Malformed UTF-8 characters, possibly incorrectly encoded');
		
		$document->toJson($options);
	}
}
