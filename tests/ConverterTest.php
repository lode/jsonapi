<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\objects\AttributesObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase {
	public function testObjectToArray_HappyPath() {
		$object = new \stdClass();
		$object->foo = 'bar';
		$object->baz = 42;
		
		$array = Converter::objectToArray($object);
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('baz', $array);
		$this->assertSame('bar', $array['foo']);
		$this->assertSame(42, $array['baz']);
	}
	
	public function testObjectToArray_MethodsAndPrivateProperties() {
		$object = new TestObject();
		$array = Converter::objectToArray($object);
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('baz', $array);
		$this->assertArrayNotHasKey('secret', $array);
		$this->assertArrayNotHasKey('method', $array);
	}
	
	public function testObjectToArray_FromInternalObject() {
		$values = ['foo'=>'bar', 'baz'=>42];
		$attributesObject = AttributesObject::fromArray($values);
		
		$array = Converter::objectToArray($attributesObject);
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('baz', $array);
		$this->assertSame('bar', $array['foo']);
		$this->assertSame(42, $array['baz']);
	}
	
	/**
	 * @dataProvider dataProviderCamelCaseToWords_HappyPath
	 */
	public function testCamelCaseToWords_HappyPath($camelCase, $expectedOutput) {
		$this->assertSame($expectedOutput, Converter::camelCaseToWords($camelCase));
	}
	
	public function dataProviderCamelCaseToWords_HappyPath() {
		return [
			['value',         'value'],
			['camelValue',    'camel Value'],
			['TitleValue',    'Title Value'],
			['VALUE',         'VALUE'],
			['eclipseRCPExt', 'eclipse RCP Ext'],
		];
	}
}

class TestObject {
	public $foo = 'bar';
	public $baz = 42;
	private $secret = 'value';
	public function method() {}
}
