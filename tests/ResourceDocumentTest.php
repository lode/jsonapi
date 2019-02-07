<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class ResourceDocumentTest extends TestCase {
	public function testSetPrimaryResource_HappyPath() {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceObject('user', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('type', $array['data']);
		$this->assertArrayHasKey('id', $array['data']);
		$this->assertSame('user', $array['data']['type']);
		$this->assertSame('42', $array['data']['id']);
		$this->assertArrayNotHasKey('attributes', $array['data']);
	}
	
	public function testSetPrimaryResource_BlocksResourceDocument() {
		$document = new ResourceDocument();
		
		$this->expectException(InputException::class);
		
		$document->setPrimaryResource(new ResourceDocument('user', 42));
	}
}
