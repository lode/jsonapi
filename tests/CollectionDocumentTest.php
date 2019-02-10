<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

class CollectionDocumentTest extends TestCase {
	public function testConstructor_NoResources() {
		$document = new CollectionDocument();
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertSame([], $array['data']);
	}
	
	public function testAdd_WithIdentifiers() {
		$document = new CollectionDocument();
		
		$document->add('user', 1);
		$document->add('user', 42);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayNotHasKey('included', $array);
		$this->assertCount(2, $array['data']);
		
		$this->assertCount(2, $array['data'][0]);
		$this->assertArrayHasKey('type', $array['data'][0]);
		$this->assertArrayHasKey('id', $array['data'][0]);
		$this->assertArrayNotHasKey('attributes', $array['data'][0]);
		$this->assertSame('user', $array['data'][0]['type']);
		$this->assertSame('1', $array['data'][0]['id']);
		
		$this->assertCount(2, $array['data'][1]);
		$this->assertArrayHasKey('type', $array['data'][1]);
		$this->assertArrayHasKey('id', $array['data'][1]);
		$this->assertArrayNotHasKey('attributes', $array['data'][1]);
		$this->assertSame('user', $array['data'][1]['type']);
		$this->assertSame('42', $array['data'][1]['id']);
	}
	
	public function testAdd_WithAttributes() {
		$document = new CollectionDocument();
		
		$document->add('user', 1, ['name' => 'foo']);
		$document->add('user', 42, ['name' => 'bar']);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayNotHasKey('included', $array);
		$this->assertCount(2, $array['data']);
		
		$firstResource = $array['data'][0];
		$this->assertCount(3, $firstResource);
		$this->assertArrayHasKey('type', $firstResource);
		$this->assertArrayHasKey('id', $firstResource);
		$this->assertArrayHasKey('attributes', $firstResource);
		$this->assertSame('user', $firstResource['type']);
		$this->assertSame('1', $firstResource['id']);
		$this->assertArrayHasKey('name', $firstResource['attributes']);
		$this->assertSame('foo', $firstResource['attributes']['name']);
		
		$secondResource = $array['data'][1];
		$this->assertCount(3, $secondResource);
		$this->assertArrayHasKey('type', $secondResource);
		$this->assertArrayHasKey('id', $secondResource);
		$this->assertArrayHasKey('attributes', $secondResource);
		$this->assertSame('user', $secondResource['type']);
		$this->assertSame('42', $secondResource['id']);
		$this->assertArrayHasKey('name', $secondResource['attributes']);
		$this->assertSame('bar', $secondResource['attributes']['name']);
	}
	
	public function testSetPaginationLinks_HappyPath() {
		$document = new CollectionDocument();
		$baseUrl  = 'https://jsonapi.org/?page=';
		
		$document->setPaginationLinks($baseUrl.'prev', $baseUrl.'next', $baseUrl.'first', $baseUrl.'last');
		
		$array = $document->toArray();
		
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
	
	/**
	 * @dataProvider dataProviderSetPaginationLinks_IndividualLinks
	 */
	public function testSetPaginationLinks_IndividualLinks($key, $previous, $next, $first, $last) {
		$document = new CollectionDocument();
		
		$document->setPaginationLinks($previous, $next, $first, $last);
		
		$array = $document->toArray();
		
		if ($key === null) {
			$this->assertArrayNotHasKey('links', $array);
		}
		else {
			$this->assertArrayHasKey('links', $array);
			$this->assertCount(1, $array['links']);
			$this->assertArrayHasKey($key, $array['links']);
			$this->assertSame('https://jsonapi.org', $array['links'][$key]);
		}
	}
	
	public function dataProviderSetPaginationLinks_IndividualLinks() {
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
		
		$this->assertCount(1, $array['data']);
		$this->assertSame('user', $array['data'][0]['type']);
		$this->assertSame('42', $array['data'][0]['id']);
		$this->assertArrayNotHasKey('attributes', $array['data'][0]);
	}
	
	public function testAddResource_WithIncluded() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$document = new CollectionDocument();
		$document->addResource($resourceObject);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('included', $array);
		$this->assertArrayHasKey('relationships', $array['data'][0]);
		$this->assertArrayHasKey('attributes', $array['included'][0]);
		$this->assertSame('42', $array['data'][0]['id']);
		$this->assertSame('24', $array['data'][0]['relationships']['foo']['data']['id']);
		$this->assertSame('24', $array['included'][0]['id']);
	}
	
	public function testAddResource_SkipIncluding() {
		$relatedResourceObject = new ResourceObject('user', 24);
		$relatedResourceObject->add('foo', 'bar');
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addRelationship('foo', $relatedResourceObject);
		
		$options = ['skipIncluding' => true];
		
		$document = new CollectionDocument();
		$document->addResource($resourceObject, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayNotHasKey('included', $array);
		$this->assertArrayHasKey('relationships', $array['data'][0]);
		$this->assertSame('42', $array['data'][0]['id']);
		$this->assertSame('24', $array['data'][0]['relationships']['foo']['data']['id']);
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
}
