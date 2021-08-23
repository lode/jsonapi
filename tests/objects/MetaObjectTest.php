<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class MetaObjectTest extends TestCase {
	public function testFromObject_HappyPath() {
		$object = new \stdClass();
		$object->foo = 'bar';
		
		$metaObject = MetaObject::fromObject($object);
		
		$array = $metaObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('bar', $array['foo']);
	}
	
	public function testIsEmpty_WithAtMembers() {
		$metaObject = new MetaObject();
		
		$this->assertTrue($metaObject->isEmpty());
		
		$metaObject->addAtMember('context', 'test');
		
		$this->assertFalse($metaObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers() {
		$metaObject = new MetaObject();
		
		$this->assertTrue($metaObject->isEmpty());
		
		$metaObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		$this->assertFalse($metaObject->isEmpty());
	}
}
