<?php

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use PHPUnit\Framework\TestCase;

class LinksObjectTest extends TestCase {
	public function testAddLinkObject_HappyPath() {
		$linkObject = new LinkObject('https://jsonapi.org');
		
		$linksObject = new LinksObject();
		$linksObject->addLinkObject($linkObject, $key='foo');
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('href', $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo']['href']);
	}
	
	public function testAddLinkObject_WithPredefinedKey() {
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->defineKey('foo');
		
		$linksObject = new LinksObject();
		$linksObject->addLinkObject($linkObject);
		
		$array = $linksObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('href', $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo']['href']);
	}
	
	public function testAddLinkObject_WithoutKey() {
		$linkObject  = new LinkObject('https://jsonapi.org');
		$linksObject = new LinksObject();
		
		$this->expectException(InputException::class);
		
		$linksObject->addLinkObject($linkObject);
	}
}
