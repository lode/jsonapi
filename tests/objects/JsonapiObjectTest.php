<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\JsonapiObject;
use PHPUnit\Framework\TestCase;

class JsonapiObjectTest extends TestCase {
	public function testAddMeta_HappyPath() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		$this->assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addMeta('foo', 'bar');
		
		$this->assertFalse($jsonapiObject->isEmpty());
		
		$array = $jsonapiObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testIsEmpty_WithAtMembers() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		$this->assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addAtMember('context', 'test');
		
		$this->assertFalse($jsonapiObject->isEmpty());
	}
}
