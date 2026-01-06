<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

final class ResourceObjectTest extends TestCase {
	public function testConstructor_ClientDocumentWithoutId(): void {
		$resourceObject = new ResourceObject('user');
		$resourceObject->add('foo', 'bar');
		
		$array = $resourceObject->toArray();
		
		parent::assertArrayNotHasKey('id', $array);
		parent::assertArrayHasKey('attributes', $array);
	}
	
	public function testFromArray_WithoutId(): void {
		$type       = 'user';
		$id         = null;
		$attributes = [
			'foo' => 'bar',
		];
		
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		parent::assertArrayNotHasKey('id', $array);
		parent::assertArrayHasKey('attributes', $array);
		parent::assertArrayHasKey('foo', $array['attributes']);
		parent::assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testFromArray_IdViaArgument(): void {
		$type       = 'user';
		$id         = 42;
		$attributes = [
			'foo' => 'bar',
		];
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		parent::assertArrayHasKey('id', $array);
		parent::assertArrayHasKey('attributes', $array);
		parent::assertArrayHasKey('foo', $array['attributes']);
		parent::assertSame('42', $array['id']);
		parent::assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testFromArray_IdViaAttributes(): void {
		$type       = 'user';
		$id         = null;
		$attributes = [
			'id'  => 42,
			'foo' => 'bar',
		];
		$resourceObject = ResourceObject::fromArray($attributes, $type, $id);
		
		$array = $resourceObject->toArray();
		
		parent::assertArrayHasKey('id', $array);
		parent::assertArrayHasKey('attributes', $array);
		parent::assertArrayHasKey('foo', $array['attributes']);
		parent::assertArrayNotHasKey('id', $array['attributes']);
		parent::assertSame('42', $array['id']);
		parent::assertSame('bar', $array['attributes']['foo']);
	}
	
	public function testHasIdentifierPropertiesOnly_Yes(): void {
		$resourceObject = new ResourceObject('user', 42);
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject();
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setAttributesObject(new AttributesObject());
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setRelationshipsObject(new RelationshipsObject());
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setLinksObject(new LinksObject());
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->setAttributesObject(new AttributesObject());
		$resourceObject->setRelationshipsObject(new RelationshipsObject());
		$resourceObject->setLinksObject(new LinksObject());
		parent::assertTrue($resourceObject->hasIdentifierPropertiesOnly());
	}
	
	public function testHasIdentifierPropertiesOnly_No(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		parent::assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', new ResourceObject('user', 24));
		parent::assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		parent::assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addLinkObject('foo', new LinkObject());
		parent::assertFalse($resourceObject->hasIdentifierPropertiesOnly());
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$resourceObject->addRelationship('baz', new ResourceObject('user', 24));
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		$resourceObject->addLinkObject('bar', new LinkObject());
		parent::assertFalse($resourceObject->hasIdentifierPropertiesOnly());
	}
	
	public function testAddRelationshipObject_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$resourceObject = new ResourceObject('user', 24);
		$resourceObject->addRelationshipObject('foo', $relationshipObject);
		
		$array = $resourceObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayHasKey('id', $array);
		parent::assertSame('user', $array['type']);
		parent::assertSame('24', $array['id']);
		
		parent::assertArrayHasKey('relationships', $array);
		parent::assertCount(1, $array['relationships']);
		parent::assertArrayHasKey('foo', $array['relationships']);
		parent::assertArrayHasKey('data', $array['relationships']['foo']);
		parent::assertArrayHasKey('type', $array['relationships']['foo']['data']);
		parent::assertArrayHasKey('id', $array['relationships']['foo']['data']);
		parent::assertSame('user', $array['relationships']['foo']['data']['type']);
		parent::assertSame('42', $array['relationships']['foo']['data']['id']);
	}
	
	public function testAddRelationshipObject_BlockDrosteEffect(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$resourceObject = new ResourceObject('user', 42);
		
		$this->expectException(DuplicateException::class);
		
		$resourceObject->addRelationshipObject('foo', $relationshipObject);
	}
	
	public function testIsEmpty_All(): void {
		$resourceObject = new ResourceObject();
		parent::assertTrue($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject('user', 42);
		parent::assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject();
		$resourceObject->add('foo', 'bar');
		parent::assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject('test', 1);
		$resourceObject->addRelationship('foo', new ResourceObject('user', 24));
		parent::assertFalse($resourceObject->isEmpty());
		
		$resourceObject = new ResourceObject();
		$resourceObject->addLink('foo', 'https://jsonapi.org');
		parent::assertFalse($resourceObject->isEmpty());
	}
}
