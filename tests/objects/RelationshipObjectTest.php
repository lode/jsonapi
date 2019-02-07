<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class RelationshipObjectTest extends TestCase {
	public function testConstructor_ToOne() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testConstructor_ToMany() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		$relationshipObject->addResource(new ResourceObject('user', 42));
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testConstructor_UnknownType() {
		$this->expectException(InputException::class);
		
		$relationshipObject = new RelationshipObject('foo');
	}
	
	public function testFromAnything_WithResourceObject() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addMeta('foo', 'bar');
		
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceIdentifierObject() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceIdentifierObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceDocument() {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceDocument('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithCollectionDocument() {
		$resourceObject     = new ResourceObject('user', 42);
		$collectionDocument = CollectionDocument::fromResources($resourceObject);
		$relationshipObject = RelationshipObject::fromAnything($collectionDocument);
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceObjects() {
		$relationshipObject = RelationshipObject::fromAnything([new ResourceObject('user', 42)]);
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithUnknownType() {
		$fakeResource = new \StdClass();
		$fakeResource->type = 'user';
		$fakeResource->id = 42;
		
		$this->expectException(InputException::class);
		
		RelationshipObject::fromAnything($fakeResource);
	}
	
	public function testSetResource_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testSetResource_RequiresToOneType() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->setResource(new ResourceObject('user', 42));
	}
	
	public function testAddResource_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		$relationshipObject->addResource(new ResourceObject('user', 42));
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testAddResource_RequiresToOneType() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->addResource(new ResourceObject('user', 42));
	}
	
	private function validateToOneRelationshipArray(array $array) {
		$this->assertNotEmpty($array);
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('type', $array['data']);
		$this->assertArrayHasKey('id', $array['data']);
		$this->assertSame('user', $array['data']['type']);
		$this->assertSame('42', $array['data']['id']);
	}
	
	private function validateToManyRelationshipArray(array $array) {
		$this->assertNotEmpty($array);
		$this->assertArrayHasKey('data', $array);
		$this->assertCount(1, $array['data']);
		$this->assertArrayHasKey(0, $array['data']);
		$this->assertArrayHasKey('type', $array['data'][0]);
		$this->assertArrayHasKey('id', $array['data'][0]);
		$this->assertSame('user', $array['data'][0]['type']);
		$this->assertSame('42', $array['data'][0]['id']);
	}
}
