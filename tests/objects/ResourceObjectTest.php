<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class ResourceObjectTest extends TestCase {
	public function testAddRelationshipObject_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$resourceObject = new ResourceObject('user', 24);
		$resourceObject->addRelationshipObject($relationshipObject, 'foo');
		
		$array = $resourceObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertSame('24', $array['id']);
		
		$this->assertArrayHasKey('relationships', $array);
		$this->assertCount(1, $array['relationships']);
		$this->assertArrayHasKey('foo', $array['relationships']);
		$this->assertArrayHasKey('data', $array['relationships']['foo']);
		$this->assertArrayHasKey('type', $array['relationships']['foo']['data']);
		$this->assertArrayHasKey('id', $array['relationships']['foo']['data']);
		$this->assertSame('user', $array['relationships']['foo']['data']['type']);
		$this->assertSame('42', $array['relationships']['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_BlockDrosteEffect() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$resourceObject = new ResourceObject('user', 42);
		
		$this->expectException(DuplicateException::class);
		
		$resourceObject->addRelationshipObject($relationshipObject, 'foo');
	}
}
