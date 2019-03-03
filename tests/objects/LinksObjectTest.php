<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
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
		
		$linksObject->addLinkString($key='foo', 'https://jsonapi.org');
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
		
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$this->expectException(DuplicateException::class);
		
		$linksObject->addLinkObject($key='foo', $linkObject);
	}
	
	public function testToArray_ExplicitlyEmpty() {
		$linksObject = new LinksObject();
		$linksObject->addLinkObject('foo', new LinkObject());
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertNull($array['foo']);
	}
}
