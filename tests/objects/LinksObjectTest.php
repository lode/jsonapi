<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;
use alsvanzelf\jsonapi\objects\LinksObject;
use PHPUnit\Framework\TestCase;

class LinksObjectTest extends TestCase {
	public function testFromObject_HappyPath() {
		$object = new \stdClass();
		$object->foo = 'https://jsonapi.org';
		
		$linksObject = LinksObject::fromObject($object);
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('https://jsonapi.org', $array['foo']);
	}
	
	public function testAppend_HappyPath() {
		$linksObject = new LinksObject();
		$linksObject->append('foo', 'https://jsonapi.org');
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]);
	}
	
	public function testAppend_BlocksReusingNonArray() {
		$linksObject = new LinksObject();
		$linksObject->add('foo', 'https://jsonapi.org');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->append('foo', 'https://jsonapi.org/2');
	}
	
	public function testAddLinkString_HappyPath() {
		$linksObject = new LinksObject();
		$linksObject->addLinkString($key='foo', 'https://jsonapi.org');
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('https://jsonapi.org', $array['foo']);
	}
	
	public function testAddLinkString_InvalidKey() {
		$linksObject = new LinksObject();
		
		$this->expectException(InputException::class);
		
		$linksObject->addLinkString($key='-foo', 'https://jsonapi.org');
	}
	
	public function testAddLinkString_ExistingKey() {
		$linksObject = new LinksObject();
		$linksObject->addLinkString($key='foo', 'https://jsonapi.org');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->addLinkString($key='foo', 'https://jsonapi.org/2');
	}
	
	public function testAddLinkObject_HappyPath() {
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$linksObject = new LinksObject();
		$linksObject->addLinkObject($key='foo', $linkObject);
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('href', $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo']['href']);
	}
	
	public function testAddLinkObject_InvalidKey() {
		$linkObject  = new LinkObject('https://jsonapi.org');
		$linksObject = new LinksObject();
		
		$this->expectException(InputException::class);
		
		$linksObject->addLinkObject($key='-foo', $linkObject);
	}
	
	public function testAddLinkObject_ExistingKey() {
		$linksObject = new LinksObject();
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linksObject->addLinkObject($key='foo', $linkObject);
		
		$linkObject = new LinkObject('https://jsonapi.org/2');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->addLinkObject($key='foo', $linkObject);
	}
	
	public function testAddLinksArray_HappyPath() {
		$linksObject = new LinksObject();
		$linksObject->addLinksArray('foo', LinksArray::fromArray(['https://jsonapi.org']));
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]);
	}
	
	public function testAddLinksArray_BlocksReusingNonArray() {
		$linksObject = new LinksObject();
		$linksObject->add('foo', 'https://jsonapi.org');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->addLinksArray('foo', LinksArray::fromArray(['https://jsonapi.org/2']));
	}
	
	public function testAppendLinkObject_HappyPath() {
		$linksObject = new LinksObject();
		$linksObject->appendLinkObject('foo', new LinkObject('https://jsonapi.org/1'));
		$linksObject->appendLinkObject('foo', new LinkObject('https://jsonapi.org/2'));
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(2, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertArrayHasKey(1, $array['foo']);
		$this->assertArrayHasKey('href', $array['foo'][0]);
		$this->assertArrayHasKey('href', $array['foo'][1]);
		$this->assertSame('https://jsonapi.org/1', $array['foo'][0]['href']);
		$this->assertSame('https://jsonapi.org/2', $array['foo'][1]['href']);
	}
	
	public function testAppendLinkObject_BlocksReusingNonArray() {
		$linksObject = new LinksObject();
		$linksObject->add('foo', 'https://jsonapi.org');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->appendLinkObject('foo', new LinkObject('https://jsonapi.org/2'));
	}
	
	public function testToArray_EmptyObject() {
		$linksObject = new LinksObject();
		$linksObject->addLinkObject('foo', new LinkObject());
		
		$array = $linksObject->toArray();
		
		$this->assertCount(0, $array);
	}
}
