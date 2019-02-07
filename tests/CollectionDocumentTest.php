<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class CollectionDocumentTest extends TestCase {
	public function testAddResource_HappyPath() {
		$document = new CollectionDocument();
		$document->addResource(new ResourceObject('user', 42));
		
		$array = $document->toArray();
		
		$this->assertCount(1, $array['data']);
		$this->assertSame('user', $array['data'][0]['type']);
		$this->assertSame('42', $array['data'][0]['id']);
		$this->assertArrayNotHasKey('attributes', $array['data'][0]);
	}
	
	public function testAddResource_RequiresIdentification() {
		$document = new CollectionDocument();
		
		$this->expectException(InputException::class);
		
		$document->addResource(new ResourceObject());
	}
	
	public function testAddResource_RequiresFullIdentification() {
		$document = new CollectionDocument();
		
		$this->expectException(InputException::class);
		
		$document->addResource(new ResourceObject('user'));
	}
}
