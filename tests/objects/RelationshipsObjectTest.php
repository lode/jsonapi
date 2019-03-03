<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class RelationshipsObjectTest extends TestCase {
	public function testAdd_HappyPath() {
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->add('foo', new ResourceObject('user', 42));
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('data', $array['foo']);
		$this->assertArrayHasKey('type', $array['foo']['data']);
		$this->assertArrayHasKey('id', $array['foo']['data']);
		$this->assertSame('user', $array['foo']['data']['type']);
		$this->assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_HappyPath() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('data', $array['foo']);
		$this->assertArrayHasKey('type', $array['foo']['data']);
		$this->assertArrayHasKey('id', $array['foo']['data']);
		$this->assertSame('user', $array['foo']['data']['type']);
		$this->assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_WithPredefinedKey() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('data', $array['foo']);
		$this->assertArrayHasKey('type', $array['foo']['data']);
		$this->assertArrayHasKey('id', $array['foo']['data']);
		$this->assertSame('user', $array['foo']['data']['type']);
		$this->assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_InvalidKey() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$this->expectException(InputException::class);
		
		$relationshipsObject->addRelationshipObject($key='-foo', $relationshipObject);
	}
	
	public function testAddRelationshipObject_MultipleRelationships() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		$relationshipsObject->addRelationshipObject($key='bar', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('bar', $array);
	}
	
	public function testAddRelationshipObject_MultipleReusingKeys() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$this->expectException(DuplicateException::class);
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
	}
	
	public function testToArray_EmptyRelationship() {
		$relationshipObject  = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		$this->assertFalse($relationshipsObject->isEmpty());
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('data', $array['foo']);
		$this->assertNull($array['foo']['data']);
	}
}
