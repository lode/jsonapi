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

final class RelationshipsObjectTest extends TestCase {
	public function testAdd_HappyPath(): void {
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
	
	public function testAddRelationshipObject_HappyPath(): void {
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
	
	public function testAddRelationshipObject_WithPredefinedKey(): void {
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
	
	public function testAddRelationshipObject_InvalidKey(): void {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$this->expectException(InputException::class);
		
		$relationshipsObject->addRelationshipObject('-foo', $relationshipObject);
	}
	
	public function testAddRelationshipObject_MultipleRelationships(): void {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		$relationshipsObject->addRelationshipObject('bar', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('bar', $array);
	}
	
	public function testAddRelationshipObject_MultipleReusingKeys(): void {
		$relationshipObject  = RelationshipObject::fromAnything(new ResourceObject('user', 42));
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$this->expectException(DuplicateException::class);
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
	}
	
	public function testToArray_EmptyRelationship(): void {
		$relationshipObject  = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipsObject = new RelationshipsObject();
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$array = $relationshipsObject->toArray();
		
		parent::assertFalse($relationshipsObject->isEmpty());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('data', $array['foo']);
		parent::assertNull($array['foo']['data']);
	}
}
