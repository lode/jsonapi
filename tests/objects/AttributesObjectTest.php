<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\AttributesObject;
use PHPUnit\Framework\TestCase;

class AttributesObjectTest extends TestCase {
	public function testFromObject_HappyPath() {
		$object = new \StdClass();
		$object->foo = 'bar';
		
		$attributesObject = AttributesObject::fromObject($object);
		
		$array = $attributesObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('bar', $array['foo']);
	}
	
	public function testAdd_HappyPath() {
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', 'bar');
		
		$array = $attributesObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('bar', $array['foo']);
	}
	
	public function testAdd_WithObject() {
		$object = new \StdClass();
		$object->bar = 'baz';
		
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', $object);
		
		$array = $attributesObject->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey('bar', $array['foo']);
		$this->assertSame('baz', $array['foo']['bar']);
	}
}
