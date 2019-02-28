<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\MetaObject;
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
}
