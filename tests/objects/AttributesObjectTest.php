<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class AttributesObjectTest extends TestCase {
	public function testFromObject_HappyPath() {
		$object = new \stdClass();
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
		$object = new \stdClass();
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
	
	/**
	 * @group Extensions
	 */
	public function testAdd_BlocksExtensionMembersViaRegularAdd() {
		$attributesObject = new AttributesObject();
		$extension        = new TestExtension();
		$extension->setNamespace('test');
		
		$this->assertSame([], $attributesObject->toArray());
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('invalid member name "test:foo"');
		
		$attributesObject->add('test:foo', 'bar');
	}
	
	/**
	 * @group Extensions
	 */
	public function testAddExtensionMember_HappyPath() {
		$attributesObject = new AttributesObject();
		$extension        = new TestExtension();
		$extension->setNamespace('test');
		
		$this->assertSame([], $attributesObject->toArray());
		
		$attributesObject->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $attributesObject->toArray();
		
		$this->assertArrayHasKey('test:foo', $array);
		$this->assertSame('bar', $array['test:foo']);
	}
}
