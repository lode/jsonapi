<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class ResourceDocumentTest extends TestCase {
	public function testConstructor_NoResource() {
		$document = new ResourceDocument();
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertNull($array['data']);
	}
	
	public function testFromObject_WithAttributesObject() {
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', 'bar');
		
		$document = ResourceDocument::fromObject($attributesObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('attributes', $array['data']);
		$this->assertArrayHasKey('foo', $array['data']['attributes']);
		$this->assertSame('bar', $array['data']['attributes']['foo']);
	}
	
	public function testAddRelationship_WithIncluded() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		
		$document = new ResourceDocument();
		$document->addRelationship('foo', $resourceObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('included', $array);
	}
	
	public function testAddRelationship_SkipIncluding() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		
		$options = ['skipIncluding' => true];
		
		$document = new ResourceDocument();
		$document->addRelationship('foo', $resourceObject, $links=[], $meta=[], $options);
		
		$array = $document->toArray();
		
		$this->assertArrayNotHasKey('included', $array);
	}
	
	public function testAddMeta_HappyPath() {
		$document = new ResourceDocument();
		$document->addMeta('foo', 'root', $level=Document::LEVEL_ROOT);
		$document->addMeta('bar', 'resource', $level=Document::LEVEL_RESOURCE);
		$document->addMeta('baz', 'jsonapi', $level=Document::LEVEL_JSONAPI);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('meta', $array['data']);
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('meta', $array['jsonapi']);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertArrayHasKey('bar', $array['data']['meta']);
		$this->assertArrayHasKey('baz', $array['jsonapi']['meta']);
		$this->assertCount(1, $array['meta']);
		$this->assertCount(1, $array['data']['meta']);
		$this->assertCount(1, $array['jsonapi']['meta']);
		$this->assertSame('root', $array['meta']['foo']);
		$this->assertSame('resource', $array['data']['meta']['bar']);
		$this->assertSame('jsonapi', $array['jsonapi']['meta']['baz']);
	}
	
	public function testAddMeta_RecreateJsonapiObject() {
		$document = new ResourceDocument();
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayNotHasKey('meta', $array['jsonapi']);
		
		$document->unsetJsonapiObject();
		
		$array = $document->toArray();
		
		$this->assertArrayNotHasKey('jsonapi', $array);
		
		$document->addMeta('baz', 'jsonapi', $level=Document::LEVEL_JSONAPI);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('meta', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['meta']);
		$this->assertSame('jsonapi', $array['jsonapi']['meta']['baz']);
	}
	
	public function testAddRelationshipObject_WithIncluded() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$document = new ResourceDocument();
		$document->addRelationshipObject('foo', $relationshipObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('included', $array);
	}
	
	public function testAddRelationshipObject_SkipIncluding() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$options = ['skipIncluding' => true];
		
		$document = new ResourceDocument();
		$document->addRelationshipObject('foo', $relationshipObject, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayNotHasKey('included', $array);
	}
	
	public function testSetRelationshipsObject_WithIncluded() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$document = new ResourceDocument();
		$document->setRelationshipsObject($relationshipsObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('included', $array);
	}
	
	public function testSetRelationshipsObject_SkipIncluding() {
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->add('foo', 'bar');
		$relationshipObject = RelationshipObject::fromAnything($resourceObject);
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$options = ['skipIncluding' => true];
		
		$document = new ResourceDocument();
		$document->setRelationshipsObject($relationshipsObject, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayNotHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_HappyPath() {
		$document = new ResourceDocument();
		$document->setPrimaryResource(new ResourceObject('user', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('type', $array['data']);
		$this->assertArrayHasKey('id', $array['data']);
		$this->assertSame('user', $array['data']['type']);
		$this->assertSame('42', $array['data']['id']);
		$this->assertArrayNotHasKey('attributes', $array['data']);
	}
	
	public function testSetPrimaryResource_WithIncluded() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$document = new ResourceDocument();
		$document->setPrimaryResource($resourceObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_SkipIncluding() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$options = ['skipIncluding' => true];
		
		$document = new ResourceDocument();
		$document->setPrimaryResource($resourceObject, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayNotHasKey('included', $array);
	}
	
	public function testSetPrimaryResource_BlocksResourceDocument() {
		$document = new ResourceDocument();
		
		$this->expectException(InputException::class);
		
		$document->setPrimaryResource(new ResourceDocument('user', 42));
	}
}
