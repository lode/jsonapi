<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use alsvanzelf\jsonapiTests\profiles\TestProfile;
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
	
	public function testIsEmpty_WithExtensionLink() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		$this->assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addExtension(new TestExtension());
		
		$this->assertFalse($jsonapiObject->isEmpty());
	}
	
	public function testIsEmpty_WithProfileLink() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		$this->assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addProfile(new TestProfile());
		
		$this->assertFalse($jsonapiObject->isEmpty());
	}
	
	public function testIsEmpty_WithExtensionMembers() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		$this->assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		$this->assertFalse($jsonapiObject->isEmpty());
	}
}
