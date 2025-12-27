<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ResourceObject;

class CollectionDocumentTest extends TestCase {
	public function testConstructor_NoResources() {
		$document = new CollectionDocument();
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertSame([], $array['data']);
	}
	
	public function testAdd_WithIdentifiers() {
		$document = new CollectionDocument();
		
		$document->add('user', 1);
		$document->add('user', 42);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayNotHasKey('included', $array);
		parent::assertCount(2, $array['data']);
		
		parent::assertCount(2, $array['data'][0]);
		parent::assertArrayHasKey('type', $array['data'][0]);
		parent::assertArrayHasKey('id', $array['data'][0]);
		parent::assertArrayNotHasKey('attributes', $array['data'][0]);
		parent::assertSame('user', $array['data'][0]['type']);
		parent::assertSame('1', $array['data'][0]['id']);
		
		parent::assertCount(2, $array['data'][1]);
		parent::assertArrayHasKey('type', $array['data'][1]);
		parent::assertArrayHasKey('id', $array['data'][1]);
		parent::assertArrayNotHasKey('attributes', $array['data'][1]);
		parent::assertSame('user', $array['data'][1]['type']);
		parent::assertSame('42', $array['data'][1]['id']);
	}
	
	public function testAdd_WithAttributes() {
		$document = new CollectionDocument();
		
		$document->add('user', 1, ['name' => 'foo']);
		$document->add('user', 42, ['name' => 'bar']);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayNotHasKey('included', $array);
		parent::assertCount(2, $array['data']);
		
		$firstResource = $array['data'][0];
		parent::assertCount(3, $firstResource);
		parent::assertArrayHasKey('type', $firstResource);
		parent::assertArrayHasKey('id', $firstResource);
		parent::assertArrayHasKey('attributes', $firstResource);
		parent::assertSame('user', $firstResource['type']);
		parent::assertSame('1', $firstResource['id']);
		parent::assertArrayHasKey('name', $firstResource['attributes']);
		parent::assertSame('foo', $firstResource['attributes']['name']);
		
		$secondResource = $array['data'][1];
		parent::assertCount(3, $secondResource);
		parent::assertArrayHasKey('type', $secondResource);
		parent::assertArrayHasKey('id', $secondResource);
		parent::assertArrayHasKey('attributes', $secondResource);
		parent::assertSame('user', $secondResource['type']);
		parent::assertSame('42', $secondResource['id']);
		parent::assertArrayHasKey('name', $secondResource['attributes']);
		parent::assertSame('bar', $secondResource['attributes']['name']);
	}
	
	public function testSetPaginationLinks_HappyPath() {
		$document = new CollectionDocument();
		$baseUrl  = 'https://jsonapi.org/?page=';
		
		$document->setPaginationLinks($baseUrl.'prev', $baseUrl.'next', $baseUrl.'first', $baseUrl.'last');
		
		$array = $document->toArray();
		
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
	
	#[DataProvider('dataProviderSetPaginationLinks_IndividualLinks')]
	public function testSetPaginationLinks_IndividualLinks($key, $previous, $next, $first, $last) {
		$document = new CollectionDocument();
		
		$document->setPaginationLinks($previous, $next, $first, $last);
		
		$array = $document->toArray();
		
		if ($key === null) {
			parent::assertArrayNotHasKey('links', $array);
		}
		else {
			parent::assertArrayHasKey('links', $array);
			parent::assertCount(1, $array['links']);
			parent::assertArrayHasKey($key, $array['links']);
			parent::assertSame('https://jsonapi.org', $array['links'][$key]);
		}
	}
	
	public static function dataProviderSetPaginationLinks_IndividualLinks() {
		return [
			['prev',  'https://jsonapi.org', null, null, null],
			['next',  null, 'https://jsonapi.org', null, null],
			['first', null, null, 'https://jsonapi.org', null],
			['last',  null, null, null, 'https://jsonapi.org'],
			[null,    null, null, null, null],
		];
	}
	
	public function testAddResource_HappyPath() {
		$document = new CollectionDocument();
		$document->addResource(new ResourceObject('user', 42));
		
		$array = $document->toArray();
		
		parent::assertCount(1, $array['data']);
		parent::assertSame('user', $array['data'][0]['type']);
		parent::assertSame('42', $array['data'][0]['id']);
		parent::assertArrayNotHasKey('attributes', $array['data'][0]);
	}
	
	public function testAddResource_WithIncluded() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$document = new CollectionDocument();
		$document->addResource($resourceObject);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('included', $array);
		parent::assertArrayHasKey('relationships', $array['data'][0]);
		parent::assertArrayHasKey('attributes', $array['included'][0]);
		parent::assertSame('42', $array['data'][0]['id']);
		parent::assertSame('24', $array['data'][0]['relationships']['foo']['data']['id']);
		parent::assertSame('24', $array['included'][0]['id']);
	}
	
	public function testAddResource_DoNotIncludeContainedResources() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$options = ['includeContainedResources' => false];
		
		$document = new CollectionDocument();
		$document->addResource($resourceObject, $options);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayNotHasKey('included', $array);
		parent::assertArrayHasKey('relationships', $array['data'][0]);
		parent::assertSame('42', $array['data'][0]['id']);
		parent::assertSame('24', $array['data'][0]['relationships']['foo']['data']['id']);
	}
	
	public function testAddResource_RequiresIdentification() {
		$document = new CollectionDocument();
		
		$this->expectException(InputException::class);
		
		$document->addResource(new ResourceObject());
	}
	
	public function testAddResource_RequiresFullIdentification() {
		$document = new CollectionDocument();
		
		$this->expectException(InputException::class);
		
		$document->addResource(new ResourceObject('user'));
	}
	
	public function testGetContainedResources_HappyPath() {
		$document = new CollectionDocument();
		
		parent::assertCount(0, $document->getContainedResources());
		
		$document->add('user', 42);
		
		parent::assertCount(1, $document->getContainedResources());
		
		$document->add('user', 24);
		
		parent::assertCount(2, $document->getContainedResources());
	}
	
	public function testGetContainedResources_NoNestedResources() {
		$document = new CollectionDocument();
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', new ResourceObject('user', 24));
		$document->addResource($resourceObject);
		
		parent::assertCount(1, $document->getContainedResources());
	}
}
