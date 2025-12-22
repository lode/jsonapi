<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class MetaObjectTest extends TestCase {
	public function testAdd_AllowsMixedValue() {
		$metaObject = new MetaObject();
		$metaObject->add('array-list', ['foo']);
		$metaObject->add('array-int-key', [42 => 'foo']);
		$metaObject->add('array-string-key', ['foo' => 'bar']);
		$metaObject->add('bool', true);
		$metaObject->add('int', 42);
		$metaObject->add('float', 4.2);
		$metaObject->add('null', null);
		$metaObject->add('object', new \stdClass);
		$metaObject->add('string', 'foo');
		
		$array = $metaObject->toArray();
		
		$this->assertCount(9, $array);
	}
	
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
