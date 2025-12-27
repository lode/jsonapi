<?php

declare(strict_types=1);

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
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertSame('bar', $array['foo']);
	}
	
	public function testAdd_HappyPath() {
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', 'bar');
		
		$array = $attributesObject->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertSame('bar', $array['foo']);
	}
	
	public function testAdd_AllowsMixedValue() {
		$attributesObject = new AttributesObject();
		$attributesObject->add('array-list', ['foo']);
		$attributesObject->add('array-int-key', [42 => 'foo']);
		$attributesObject->add('array-string-key', ['foo' => 'bar']);
		$attributesObject->add('bool', true);
		$attributesObject->add('int', 42);
		$attributesObject->add('float', 4.2);
		$attributesObject->add('null', null);
		$attributesObject->add('object', new \stdClass);
		$attributesObject->add('string', 'foo');
		
		$array = $attributesObject->toArray();
		
		parent::assertCount(9, $array);
	}
	
	public function testAdd_WithObject() {
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$attributesObject = new AttributesObject();
		$attributesObject->add('foo', $object);
		
		$array = $attributesObject->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		
		parent::assertCount(1, $array['foo']);
		parent::assertArrayHasKey('bar', $array['foo']);
		parent::assertSame('baz', $array['foo']['bar']);
	}
	
	/**
	 * @group Extensions
	 */
	public function testAdd_BlocksExtensionMembersViaRegularAdd() {
		$attributesObject = new AttributesObject();
		$extension        = new TestExtension();
		$extension->setNamespace('test');
		
		parent::assertSame([], $attributesObject->toArray());
		
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
		
		parent::assertSame([], $attributesObject->toArray());
		
		$attributesObject->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $attributesObject->toArray();
		
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertSame('bar', $array['test:foo']);
	}
}
