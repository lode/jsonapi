<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class RelationshipObjectTest extends TestCase {
	public function testConstructor_ToOne(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testConstructor_ToMany(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		$relationshipObject->addResource(new ResourceObject('user', 42));
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceObject(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addMeta('foo', 'bar');
		
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceIdentifierObject(): void {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceIdentifierObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceDocument(): void {
		$relationshipObject = RelationshipObject::fromAnything(new ResourceDocument('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithCollectionDocument(): void {
		$resourceObject     = new ResourceObject('user', 42);
		$collectionDocument = CollectionDocument::fromResources($resourceObject);
		$relationshipObject = RelationshipObject::fromAnything($collectionDocument);
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromAnything_WithResourceObjects(): void {
		$relationshipObject = RelationshipObject::fromAnything([new ResourceObject('user', 42)]);
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testFromResource_ToMany(): void {
		$resourceObject = new ResourceObject('user', 42);
		$type           = RelationshipTypeEnum::ToMany;
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, type: $type);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertCount(1, $array['data']);
		parent::assertArrayHasKey('type', $array['data'][0]);
		parent::assertArrayHasKey('id', $array['data'][0]);
		parent::assertSame('user', $array['data'][0]['type']);
		parent::assertSame('42', $array['data'][0]['id']);
	}
	
	public function testFromResource_WithLinks(): void {
		$resourceObject = new ResourceObject('user', 42);
		$links          = ['self' => 'https://jsonapi.org'];
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, $links);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(1, $array['links']);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertSame('https://jsonapi.org', $array['links']['self']);
	}
	
	public function testFromResource_WithMeta(): void {
		$resourceObject = new ResourceObject('user', 42);
		$meta          = ['foo' => 'bar'];
		
		$relationshipObject = RelationshipObject::fromResource($resourceObject, meta: $meta);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testFromCollectionDocument_WithMeta(): void {
		$collectionDocument = CollectionDocument::fromResources(new ResourceObject('user', 42));
		$meta               = ['foo' => 'bar'];
		
		$relationshipObject = RelationshipObject::fromCollectionDocument($collectionDocument, meta: $meta);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertCount(1, $array['meta']);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testSetSelfLink_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setSelfLink('https://jsonapi.org');
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertSame('https://jsonapi.org', $array['links']['self']);
	}
	
	public function testSetRelatedLink_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setRelatedLink('https://jsonapi.org');
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('related', $array['links']);
		parent::assertSame('https://jsonapi.org', $array['links']['related']);
	}
	
	public function testSetPaginationLinks_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		$baseUrl            = 'https://jsonapi.org/?page=';
		
		$relationshipObject->setPaginationLinks($baseUrl.'prev', $baseUrl.'next', $baseUrl.'first', $baseUrl.'last');
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(4, $array['links']);
		parent::assertArrayHasKey('prev', $array['links']);
		parent::assertArrayHasKey('next', $array['links']);
		parent::assertArrayHasKey('first', $array['links']);
		parent::assertArrayHasKey('last', $array['links']);
		parent::assertSame($baseUrl.'prev', $array['links']['prev']);
		parent::assertSame($baseUrl.'next', $array['links']['next']);
		parent::assertSame($baseUrl.'first', $array['links']['first']);
		parent::assertSame($baseUrl.'last', $array['links']['last']);
	}
	
	public function testSetPaginationLinks_BlockedOnToOne(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->setPaginationLinks('foo');
	}
	
	public function testAddMeta_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		parent::assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addMeta('foo', 'bar');
		
		parent::assertFalse($relationshipObject->isEmpty());
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testHasResource_ToMany(): void {
		$resourceObject = new ResourceObject('user', 42);
		
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		$relationshipObject->addResource($resourceObject);
		
		parent::assertTrue($relationshipObject->hasResource($resourceObject));
		parent::assertTrue($relationshipObject->hasResource(new ResourceObject('user', 42)));
		parent::assertFalse($relationshipObject->hasResource(new ResourceObject('user', 24)));
	}
	
	public function testGetContainedResources_SkipsResourceIdentifierObjects(): void {
		$relationshipObject           = new RelationshipObject(RelationshipTypeEnum::ToMany);
		$resourceIdentifierObject     = new ResourceIdentifierObject('user', 24);
		$resourceObjectIdentifierOnly = new ResourceObject('user', 42);
		$resourceObjectWithAttributes = new ResourceObject('user', 42);
		$resourceObjectWithAttributes->add('foo', 'bar');
		
		parent::assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceIdentifierObject);
		
		parent::assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceObjectIdentifierOnly);
		
		parent::assertCount(0, $relationshipObject->getNestedContainedResourceObjects());
		
		$relationshipObject->addResource($resourceObjectWithAttributes);
		
		parent::assertCount(1, $relationshipObject->getNestedContainedResourceObjects());
	}
	
	public function testSetResource_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		$relationshipObject->setResource(new ResourceObject('user', 42));
		
		$this->validateToOneRelationshipArray($relationshipObject->toArray());
	}
	
	public function testSetResource_RequiresToOneType(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->setResource(new ResourceObject('user', 42));
	}
	
	public function testAddResource_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		$relationshipObject->addResource(new ResourceObject('user', 42));
		
		$this->validateToManyRelationshipArray($relationshipObject->toArray());
	}
	
	public function testAddResource_RequiresToOneType(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		$this->expectException(InputException::class);
		
		$relationshipObject->addResource(new ResourceObject('user', 42));
	}
	
	public function testAddLinkObject_HappyPath(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		parent::assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addLinkObject('foo', new LinkObject('https://jsonapi.org'));
		
		parent::assertFalse($relationshipObject->isEmpty());
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('foo', $array['links']);
		parent::assertArrayHasKey('href', $array['links']['foo']);
		parent::assertArrayNotHasKey('meta', $array['links']['foo']);
		parent::assertSame('https://jsonapi.org', $array['links']['foo']['href']);
	}
	
	public function testToArray_EmptyResource(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertNull($array['data']);
	}
	
	public function testToArray_EmptyResources(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToMany);
		
		$array = $relationshipObject->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertIsArray($array['data']);
	}
	
	public function testIsEmpty_WithAtMembers(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		parent::assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addAtMember('context', 'test');
		
		parent::assertFalse($relationshipObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers(): void {
		$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		
		parent::assertTrue($relationshipObject->isEmpty());
		
		$relationshipObject->addExtensionMember(parent::createStub(ExtensionInterface::class), 'foo', 'bar');
		
		parent::assertFalse($relationshipObject->isEmpty());
	}
	
	/**
	 * @param array<string, mixed> $array
	 */
	private function validateToOneRelationshipArray(array $array): void {
		parent::assertNotEmpty($array);
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('type', $array['data']);
		parent::assertArrayHasKey('id', $array['data']);
		parent::assertSame('user', $array['data']['type']);
		parent::assertSame('42', $array['data']['id']);
	}
	
	/**
	 * @param array<string, mixed> $array
	 */
	private function validateToManyRelationshipArray(array $array): void {
		parent::assertNotEmpty($array);
		parent::assertArrayHasKey('data', $array);
		parent::assertCount(1, $array['data']);
		parent::assertArrayHasKey(0, $array['data']);
		parent::assertArrayHasKey('type', $array['data'][0]);
		parent::assertArrayHasKey('id', $array['data'][0]);
		parent::assertSame('user', $array['data'][0]['type']);
		parent::assertSame('42', $array['data'][0]['id']);
	}
}
