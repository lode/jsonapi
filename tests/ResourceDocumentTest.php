<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

final class ResourceDocumentTest extends TestCase {
	public function testConstructor_NoResource(): void {
		$document = new ResourceDocument();
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertNull($array['data']);
	}
	
	public function testFromObject_WithAttributesObject(): void {
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', 'bar');
		
		$document = ResourceDocument::fromObject($attributesObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('attributes', $array['data']);
		parent::assertArrayHasKey('foo', $array['data']['attributes']);
		parent::assertSame('bar', $array['data']['attributes']['foo']);
	}
	
	public function testAdd_HappyPath(): void {
		$document = new ResourceDocument('user', 42);
		$document->add('foo', 'bar');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('attributes', $array['data']);
		parent::assertArrayHasKey('foo', $array['data']['attributes']);
		parent::assertSame('bar', $array['data']['attributes']['foo']);
	}
	
	public function testAdd_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		
		$document->add('foo', 'bar');
	}
	
	/**
	 * @group Extensions
	 */
	public function testAdd_BlocksExtensionMembersViaRegularAdd(): void {
		$document = new ResourceDocument();
		$document->applyExtension(parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']));
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('invalid member name "test:foo"');
		
		$document->add('test:foo', 'bar');
	}
	
	public function testAddRelationship_WithIncluded(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		
		$document = new ResourceDocument('test', 1);
		$document->addRelationship('foo', $resourceObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('included', $array);
	}
	
	public function testAddRelationship_DoNotIncludeContainedResources(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		
		$options = ['includeContainedResources' => false];
		
		$document = new ResourceDocument('test', 1);
		$document->addRelationship('foo', $resourceObject, options: $options);
		
		$array = $document->toArray();
		
		parent::assertArrayNotHasKey('included', $array);
	}
	
	public function testAddRelationship_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->addRelationship('foo', null);
	}
	
	public function testAddLink_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->addLink('foo', null);
	}
	
	public function testSetSelfLink_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->setSelfLink('https://jsonapi.org');
	}
	
	public function testSetAttributesObject_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->setAttributesObject(new AttributesObject());
	}
	
	public function testAddRelationshipObject_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->addRelationshipObject('foo', new RelationshipObject(RelationshipTypeEnum::ToOne));
	}
	
	public function testSetRelationshipsObject_IdentifierOnlyObject(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceIdentifierObject('user', 42));
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('the resource is an identifier-only object');
		
		$document->setRelationshipsObject(new RelationshipsObject());
	}
	
	public function testAddMeta_HappyPath(): void {
		$document = new ResourceDocument();
		$document->addMeta('foo', 'root', DocumentLevelEnum::Root);
		$document->addMeta('bar', 'resource', DocumentLevelEnum::Resource);
		$document->addMeta('baz', 'jsonapi', DocumentLevelEnum::Jsonapi);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('meta', $array['data']);
		parent::assertArrayHasKey('meta', $array['jsonapi']);
		parent::assertSame(['foo' => 'root'], $array['meta']);
		parent::assertSame(['bar' => 'resource'], $array['data']['meta']);
		parent::assertSame(['baz' => 'jsonapi'], $array['jsonapi']['meta']);
	}
	
	public function testAddMeta_RecreateJsonapiObject(): void {
		$document = new ResourceDocument();
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayNotHasKey('meta', $array['jsonapi']);
		
		$document->unsetJsonapiObject();
		
		$array = $document->toArray();
		
		parent::assertArrayNotHasKey('jsonapi', $array);
		
		$document->addMeta('baz', 'jsonapi', DocumentLevelEnum::Jsonapi);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('meta', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['meta']);
		parent::assertSame('jsonapi', $array['jsonapi']['meta']['baz']);
	}
	
	public function testSetLocalId_HappyPath(): void {
		$document = new ResourceDocument();
		$document->setType('user');
		$document->setLocalId('42');
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('lid', $array['data']);
		parent::assertArrayNotHasKey('id', $array['data']);
		parent::assertSame('42', $array['data']['lid']);
	}
	
	public function testAddRelationshipObject_WithIncluded(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$document = new ResourceDocument('test', 1);
		$document->addRelationshipObject('foo', $relationshipObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('included', $array);
	}
	
	public function testAddRelationshipObject_DoNotIncludeContainedResources(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$options = ['includeContainedResources' => false];
		
		$document = new ResourceDocument('test', 1);
		$document->addRelationshipObject('foo', $relationshipObject, $options);
		
		$array = $document->toArray();
		
		parent::assertArrayNotHasKey('included', $array);
	}
	
	public function testSetRelationshipsObject_WithIncluded(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$document = new ResourceDocument();
		$document->setRelationshipsObject($relationshipsObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('included', $array);
	}
	
	public function testSetRelationshipsObject_DoNotIncludeContainedResources(): void {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$options = ['includeContainedResources' => false];
		
		$document = new ResourceDocument();
		$document->setRelationshipsObject($relationshipsObject, $options);
		
		$array = $document->toArray();
		
		parent::assertArrayNotHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_HappyPath(): void {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceObject('user', 42));
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('type', $array['data']);
		parent::assertArrayHasKey('id', $array['data']);
		parent::assertSame('user', $array['data']['type']);
		parent::assertSame('42', $array['data']['id']);
		parent::assertArrayNotHasKey('attributes', $array['data']);
	}
	
	public function testSetPrimaryResource_WithIncluded(): void {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$document = new ResourceDocument();
		$document->setPrimaryResource($resourceObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_DoNotIncludeContainedResources(): void {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$options = ['includeContainedResources' => false];
		
		$document = new ResourceDocument();
		$document->setPrimaryResource($resourceObject, $options);
		
		$array = $document->toArray();
		
		parent::assertArrayNotHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_BlocksResourceDocument(): void {
		$document = new ResourceDocument();
		
		$this->expectException(InputException::class);
		
		$document->setPrimaryResource(new ResourceDocument('user', 42));
	}
}
