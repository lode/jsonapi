<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class ResourceObjectTest extends TestCase {
	public function testFromArray_WithoutId() {
		$type       = 'user';
		$id         = null;
		$attributes = [
			'foo' => 'bar',
		];
		
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		$this->assertArrayHasKey('id', $array);
		$this->assertArrayHasKey('attributes', $array);
		$this->assertArrayHasKey('foo', $array['attributes']);
		$this->assertNull($array['id']);
		$this->assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testFromArray_IdViaArgument() {
		$type       = 'user';
		$id         = 42;
		$attributes = [
			'foo' => 'bar',
		];
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		$this->assertArrayHasKey('id', $array);
		$this->assertArrayHasKey('attributes', $array);
		$this->assertArrayHasKey('foo', $array['attributes']);
		$this->assertSame('42', $array['id']);
		$this->assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testFromArray_IdViaAttributes() {
		$type       = 'user';
		$id         = null;
		$attributes = [
			'id'  => 42,
			'foo' => 'bar',
		];
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		$this->assertArrayHasKey('id', $array);
		$this->assertArrayHasKey('attributes', $array);
		$this->assertArrayHasKey('foo', $array['attributes']);
		$this->assertArrayNotHasKey('id', $array['attributes']);
		$this->assertSame('42', $array['id']);
		$this->assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testHasIdentifierPropertiesOnly_Yes() {
		$resourceObject = new ResourceObject('user', 42);
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject();
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setAttributesObject(new AttributesObject());
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setRelationshipsObject(new RelationshipsObject());
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setLinksObject(new LinksObject());
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setAttributesObject(new AttributesObject());
		$resourceObject->setRelationshipsObject(new RelationshipsObject());
		$resourceObject->setLinksObject(new LinksObject());
		$this->assertTrue($resourceObject->hasIdentifierPropertiesOnly());
	}
	
	public function testHasIdentifierPropertiesOnly_No() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$this->assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', new ResourceObject('user', 24));
		$this->assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		$this->assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addLinkObject('foo', new LinkObject());
		$this->assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$resourceObject->addRelationship('baz', new ResourceObject('user', 24));
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		$resourceObject->addLinkObject('bar', new LinkObject());
		$this->assertFalse($resourceObject->hasIdentifierPropertiesOnly());
	}
	
	public function testAddRelationshipObject_HappyPath() {
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$resourceObject = new ResourceObject('user', 24);
		$resourceObject->addRelationshipObject('foo', $relationshipObject);
		
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
		
		$resourceObject->addRelationshipObject('foo', $relationshipObject);
	}
	
	public function testIsEmpty_All() {
		$resourceObject = new ResourceObject();
		$this->assertTrue($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject('user', 42);
		$this->assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject();
		$resourceObject->add('foo', 'bar');
		$this->assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject();
		$resourceObject->addRelationship('foo', new ResourceObject('user', 24));
		$this->assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject();
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		$this->assertFalse($resourceObject->isEmpty());
	}
}
