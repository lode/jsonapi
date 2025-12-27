<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\MetaDocument;
use PHPUnit\Framework\TestCase;

class MetaDocumentTest extends TestCase {
	public function testConstructor_NoMeta() {
		$document = new MetaDocument();
		
		$array = $document->toArray();
		parent::assertArrayHasKey('meta', $array);
		
		// verify meta is an object, not an array
		$json = $document->toJson();
		parent::assertSame('{"jsonapi":{"version":"1.1"},"meta":{}}', $json);
	}
	
	public function testFromArray_HappyPath() {
		$document = MetaDocument::fromArray(['foo' => 'bar']);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testFromObject_HappyPath() {
		$object = new \stdClass();
		$object->foo = 'bar';
		
		$document = MetaDocument::fromObject($object);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAddMeta_HappyPath() {
		$document = new MetaDocument();
		$document->addMeta('foo', 'bar');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testAdd_HappyPath() {
		$document = new MetaDocument();
		$document->add('foo', 'bar');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
		
		parent::assertCount(2, $array);
		parent::assertArrayNotHasKey('data', $array);
		parent::assertArrayHasKey('jsonapi', $array);
	}
}
