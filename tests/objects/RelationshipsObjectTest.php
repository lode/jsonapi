<?php

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class RelationshipsObjectTest extends TestCase {
	public function testAddRelationshipObject_HappyPath() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject($relationshipObject, $key='foo');
		
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
		$relationshipObject->defineKey('foo');
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject($relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('data', $array['foo']);
		$this->assertArrayHasKey('type', $array['foo']['data']);
		$this->assertArrayHasKey('id', $array['foo']['data']);
		$this->assertSame('user', $array['foo']['data']['type']);
		$this->assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_WithoutKey() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$this->expectException(InputException::class);
		
		$relationshipsObject->addRelationshipObject($relationshipObject);
	}
	
	public function testAddRelationshipObject_MultipleRelationships() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($relationshipObject, $key='foo');
		$relationshipsObject->addRelationshipObject($relationshipObject, $key='bar');
		
		$array = $relationshipsObject->toArray();
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('bar', $array);
	}
	
	public function testAddRelationshipObject_MultipleReusingKeys() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($relationshipObject, $key='foo');
		
		$this->expectException(DuplicateException::class);
		
		$relationshipsObject->addRelationshipObject($relationshipObject, $key='foo');
	}
}
