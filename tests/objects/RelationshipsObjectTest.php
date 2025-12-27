<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
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
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('data', $array['foo']);
		parent::assertArrayHasKey('type', $array['foo']['data']);
		parent::assertArrayHasKey('id', $array['foo']['data']);
		parent::assertSame('user', $array['foo']['data']['type']);
		parent::assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_HappyPath() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('data', $array['foo']);
		parent::assertArrayHasKey('type', $array['foo']['data']);
		parent::assertArrayHasKey('id', $array['foo']['data']);
		parent::assertSame('user', $array['foo']['data']['type']);
		parent::assertSame('42', $array['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_WithPredefinedKey() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('data', $array['foo']);
		parent::assertArrayHasKey('type', $array['foo']['data']);
		parent::assertArrayHasKey('id', $array['foo']['data']);
		parent::assertSame('user', $array['foo']['data']['type']);
		parent::assertSame('42', $array['foo']['data']['id']);
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
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('bar', $array);
	}
	
	public function testAddRelationshipObject_MultipleReusingKeys() {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$this->expectException(DuplicateException::class);
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
	}
	
	public function testToArray_EmptyRelationship() {
		$relationshipObject  = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject($key='foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		parent::assertFalse($relationshipsObject->isEmpty());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('data', $array['foo']);
		parent::assertNull($array['foo']['data']);
	}
}
