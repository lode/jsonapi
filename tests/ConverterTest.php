<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ConverterTest extends TestCase {
	public function testObjectToArray_HappyPath(): void {
		$object = new \stdClass();
		$object->foo = 'bar';
		$object->baz = 42;
		
		$array = Converter::objectToArray($object);
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('baz', $array);
		parent::assertSame('bar', $array['foo']);
		parent::assertSame(42, $array['baz']);
	}
	
	public function testObjectToArray_MethodsAndPrivateProperties(): void {
		$object = new class {
			public string $foo = 'bar';
			public int $baz = 42;
			private string $secret = 'value'; // @phpstan-ignore property.onlyWritten
			public function method(): void {}
		};
		
		$array = Converter::objectToArray($object);
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('baz', $array);
		parent::assertArrayNotHasKey('secret', $array);
		parent::assertArrayNotHasKey('method', $array);
	}
	
	public function testObjectToArray_FromInternalObject(): void {
		$values = ['foo'=>'bar', 'baz'=>42];
		$attributesObject = AttributesObject::fromArray($values);
		
		$array = Converter::objectToArray($attributesObject);
		
		parent::assertCount(2, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('baz', $array);
		parent::assertSame('bar', $array['foo']);
		parent::assertSame(42, $array['baz']);
	}
	
	#[DataProvider('dataProviderCamelCaseToWords_HappyPath')]
	public function testCamelCaseToWords_HappyPath(string $camelCase, string $expectedOutput): void {
		parent::assertSame($expectedOutput, Converter::camelCaseToWords($camelCase));
	}
	
	/**
	 * @return \Iterator<(int | string), array{string, string}>
	 */
	public static function dataProviderCamelCaseToWords_HappyPath(): \Iterator {
		yield ['value',         'value'];
		yield ['camelValue',    'camel Value'];
		yield ['TitleValue',    'Title Value'];
		yield ['VALUE',         'VALUE'];
		yield ['eclipseRCPExt', 'eclipse RCP Ext'];
	}
	
	#[Group('Extensions')]
	#[Group('Profiles')]
	public function testPrepareContentType_HappyPath(): void {
		parent::assertSame(ContentTypeEnum::Official->value, Converter::prepareContentType(ContentTypeEnum::Official, [], []));
	}
	
	#[Group('Extensions')]
	public function testPrepareContentType_WithExtensionStringLink(): void {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getOfficialLink' => 'bar']);
		
		parent::assertSame(ContentTypeEnum::Official->value.'; ext="bar"', Converter::prepareContentType(ContentTypeEnum::Official, [$extension], []));
	}
	
	#[Group('Profiles')]
	public function testPrepareContentType_WithProfileStringLink(): void {
		$profile = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'bar']);
		
		parent::assertSame(ContentTypeEnum::Official->value.'; profile="bar"', Converter::prepareContentType(ContentTypeEnum::Official, [], [$profile]));
	}
	
	#[Group('Extensions')]
	#[Group('Profiles')]
	public function testPrepareContentType_WithMultipleExtensionsAndProfiles(): void {
		$extension1 = parent::createConfiguredStub(ExtensionInterface::class, ['getOfficialLink' => 'bar']);
		$extension2 = parent::createConfiguredStub(ExtensionInterface::class, ['getOfficialLink' => 'baz']);
		$profile1   = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'bar']);
		$profile2   = parent::createConfiguredStub(ProfileInterface::class, ['getOfficialLink' => 'baz']);
		
		$expectedContentType  = ContentTypeEnum::Official->value.'; ext="bar baz"; profile="bar baz"';
		$convertedContentType = Converter::prepareContentType(ContentTypeEnum::Official, [$extension1, $extension2], [$profile1, $profile2]);
		
		parent::assertSame($expectedContentType, $convertedContentType);
	}
}
