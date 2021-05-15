<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use alsvanzelf\jsonapiTests\profiles\TestProfile;
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
	
	/**
	 * @group Extensions
	 * @group Profiles
	 */
	public function testPrepareContentType_HappyPath() {
		$this->assertSame('foo', Converter::prepareContentType('foo', [], []));
	}
	
	/**
	 * @group Extensions
	 */
	public function testPrepareContentType_WithExtensionStringLink() {
		$extension = new TestExtension();
		$extension->setOfficialLink('bar');
		
		$this->assertSame('foo; ext="bar"', Converter::prepareContentType('foo', [$extension], []));
	}
	
	/**
	 * @group Profiles
	 */
	public function testPrepareContentType_WithProfileStringLink() {
		$profile = new TestProfile();
		$profile->setOfficialLink('bar');
		
		$this->assertSame('foo; profile="bar"', Converter::prepareContentType('foo', [], [$profile]));
	}
	
	/**
	 * @group Extensions
	 * @group Profiles
	 */
	public function testPrepareContentType_WithMultipleExtensionsAndProfiles() {
		$extension1 = new TestExtension();
		$extension1->setOfficialLink('bar');
		
		$extension2 = new TestExtension();
		$extension2->setOfficialLink('baz');
		
		$profile1 = new TestProfile();
		$profile1->setOfficialLink('bar');
		
		$profile2 = new TestProfile();
		$profile2->setOfficialLink('baz');
		
		$this->assertSame('foo; ext="bar baz"; profile="bar baz"', Converter::prepareContentType('foo', [$extension1, $extension2], [$profile1, $profile2]));
	}
	
	/**
	 * test method while it is part of the interface
	 * @group Profiles
	 */
	public function testMergeProfilesInContentType_HappyPath() {
		$profile = new TestProfile();
		$profile->setOfficialLink('bar');
		
		$this->assertSame('foo; profile="bar"', Converter::mergeProfilesInContentType('foo', [$profile]));
	}
}

class TestObject {
	public $foo = 'bar';
	public $baz = 42;
	private $secret = 'value';
	public function method() {}
}
