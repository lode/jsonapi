<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\MetaDocument;
use PHPUnit\Framework\TestCase;

class MetaDocumentTest extends TestCase {
	public function testConstructor_NoMeta() {
		$document = new MetaDocument();
		
		$array = $document->toArray();
		$this->assertArrayHasKey('meta', $array);
		
		// verify meta is an object, not an array
		$json = $document->toJson();
		$this->assertSame('{"jsonapi":{"version":"1.0"},"meta":{}}', $json);
	}
	
	public function testAddMeta_HappyPath() {
		$document = new MetaDocument();
		$document->addMeta('foo', 'bar');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertCount(1, $array['meta']);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAdd_HappyPath() {
		$document = new MetaDocument();
		$document->add('foo', 'bar');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertCount(1, $array['meta']);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
		
		$this->assertCount(2, $array);
		$this->assertArrayNotHasKey('data', $array);
		$this->assertArrayHasKey('jsonapi', $array);
	}
}
