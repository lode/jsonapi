<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
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
		$fakeResource = new \stdClass();
		$fakeResource->type = 'user';
		$fakeResource->id = 42;
		
		$this->expectException(InputException::class);
		
		RelationshipObject::fromAnything($fakeResource);
	}
	
	public function testFromResource_ToMany() {
		$resourceObject = new ResourceObject('user', 42);
		$type           = RelationshipObject::TO_MANY;
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, $links=[], $meta=[], $type);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertCount(1, $array['data']);
		$this->assertArrayHasKey('type', $array['data'][0]);
		$this->assertArrayHasKey('id', $array['data'][0]);
		$this->assertSame('user', $array['data'][0]['type']);
		$this->assertSame('42', $array['data'][0]['id']);
	}
	
	public function testFromResource_WithLinks() {
		$resourceObject = new ResourceObject('user', 42);
		$links          = ['self' => 'https://jsonapi.org'];
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, $links);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(1, $array['links']);
		$this->assertArrayHasKey('self', $array['links']);
		$this->assertSame('https://jsonapi.org', $array['links']['self']);
	}
	
	public function testFromResource_WithMeta() {
		$resourceObject = new ResourceObject('user', 42);
		$meta          = ['foo' => 'bar'];
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, $links=[], $meta);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertCount(1, $array['meta']);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testFromCollectionDocument_WithMeta() {
		$collectionDocument = CollectionDocument::fromResources(new ResourceObject('user', 42));
		$meta               = ['foo' => 'bar'];
		
		$relationshipObject = RelationshipObject::fromCollectionDocument($collectionDocument, $links=[], $meta);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertCount(1, $array['meta']);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testSetSelfLink_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setSelfLink('https://jsonapi.org');
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('self', $array['links']);
		$this->assertSame('https://jsonapi.org', $array['links']['self']);
	}
	
	public function testSetRelatedLink_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setRelatedLink('https://jsonapi.org');
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('related', $array['links']);
		$this->assertSame('https://jsonapi.org', $array['links']['related']);
	}
	
	public function testSetPaginationLinks_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		$baseUrl            = 'https://jsonapi.org/?page=';
		
		$relationshipObject->setPaginationLinks($baseUrl.'prev', $baseUrl.'next', $baseUrl.'first', $baseUrl.'last');
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(4, $array['links']);
		$this->assertArrayHasKey('prev', $array['links']);
		$this->assertArrayHasKey('next', $array['links']);
		$this->assertArrayHasKey('first', $array['links']);
		$this->assertArrayHasKey('last', $array['links']);
		$this->assertSame($baseUrl.'prev', $array['links']['prev']);
		$this->assertSame($baseUrl.'next', $array['links']['next']);
		$this->assertSame($baseUrl.'first', $array['links']['first']);
		$this->assertSame($baseUrl.'last', $array['links']['last']);
	}
	
	public function testSetPaginationLinks_BlockedOnToOne() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->setPaginationLinks('foo');
	}
	
	public function testAddMeta_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$this->assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addMeta('foo', 'bar');
		
		$this->assertFalse($relationshipObject->isEmpty());
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testHasResource_ToMany() {
		$resourceObject = new ResourceObject('user', 42);
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		$relationshipObject->addResource($resourceObject);
		
		$this->assertTrue($relationshipObject->hasResource($resourceObject));
		$this->assertTrue($relationshipObject->hasResource(new ResourceObject('user', 42)));
		$this->assertFalse($relationshipObject->hasResource(new ResourceObject('user', 24)));
	}
	
	public function testGetContainedResources_SkipsResourceIdentifierObjects() {
		$relationshipObject           = new RelationshipObject(RelationshipObject::TO_MANY);
		$resourceIdentifierObject     = new ResourceIdentifierObject('user', 24);
		$resourceObjectIdentifierOnly = new ResourceObject('user', 42);
		$resourceObjectWithAttributes = new ResourceObject('user', 42);
		$resourceObjectWithAttributes->add('foo', 'bar');
		
		$this->assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceIdentifierObject);
		
		$this->assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceObjectIdentifierOnly);
		
		$this->assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceObjectWithAttributes);
		
		$this->assertCount(1, $relationshipObject->getNestedContainedResourceObjects());
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
	
	public function testAddLinkObject_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$this->assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addLinkObject('foo', new LinkObject('https://jsonapi.org'));
		
		$this->assertFalse($relationshipObject->isEmpty());
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('foo', $array['links']);
		$this->assertArrayHasKey('href', $array['links']['foo']);
		$this->assertArrayNotHasKey('meta', $array['links']['foo']);
		$this->assertSame('https://jsonapi.org', $array['links']['foo']['href']);
	}
	
	public function testToArray_EmptyResource() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertNull($array['data']);
	}
	
	public function testToArray_EmptyResources() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_MANY);
		
		$array = $relationshipObject->toArray();
		
		$this->assertArrayHasKey('data', $array);
		if (method_exists($this, 'assertIsArray')) {
			$this->assertIsArray($array['data']);
		}
		else {
			$this->assertInternalType('array', $array['data']);
		}
	}
	
	public function testIsEmpty_WithAtMembers() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		
		$this->assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addAtMember('context', 'test');
		
		$this->assertFalse($relationshipObject->isEmpty());
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
