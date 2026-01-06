<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\enums\ObjectContainerEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase {
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_HappyPath(): void {
		$validator = new Validator();
		
		$fieldNames      = ['foo'];
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$fieldNames      = ['bar'];
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	public function testClaimUsedFields_EnforceNamespace(): void {
		$validator  = new Validator();
		$fieldNames = ['foo'];
		
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$this->expectException(DuplicateException::class);
		
		$objectContainer = ObjectContainerEnum::Relationships;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_AllowSameContainer(): void {
		$validator  = new Validator();
		$fieldNames = ['foo'];
		
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_OptionForReusingTypeField(): void {
		$validator  = new Validator();
		$fieldNames = ['type'];
		
		$objectContainer = ObjectContainerEnum::Type;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$objectContainer = ObjectContainerEnum::Attributes;
		$options         = ['enforceTypeFieldNamespace' => false];
		$validator->claimUsedFields($fieldNames, $objectContainer, $options);
	}
	
	#[DoesNotPerformAssertions]
	public function testClearUsedFields_HappyPath(): void {
		$validator       = new Validator();
		
		$fieldNames      = ['foo'];
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$validator->clearUsedFields($objectContainer);
	}
	
	public function testClearUsedFields_FreesForAnotherNamespace(): void {
		$validator  = new Validator();
		
		$fieldNames      = ['foo', 'bar'];
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$thrown = false;
		try {
			$fieldNames      = ['bar'];
			$objectContainer = ObjectContainerEnum::Relationships;
			$validator->claimUsedFields($fieldNames, $objectContainer);
		}
		catch (DuplicateException) {
			$thrown = true;
		}
		parent::assertTrue($thrown);
		
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->clearUsedFields($objectContainer);
		
		$fieldNames      = ['foo'];
		$objectContainer = ObjectContainerEnum::Attributes;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$fieldNames      = ['bar'];
		$objectContainer = ObjectContainerEnum::Relationships;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$this->expectException(DuplicateException::class);
		
		$fieldNames      = ['foo'];
		$objectContainer = ObjectContainerEnum::Relationships;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedResourceIdentifier_HappyPath(): void {
		$validator = new Validator();
		
		$resource = new ResourceObject('foo', 1);
		$validator->claimUsedResourceIdentifier($resource);
		
		$resource = new ResourceObject('foo', 2);
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	public function testClaimUsedResourceIdentifier_RequiresIdentification(): void {
		$validator = new Validator();
		
		$resource = new ResourceObject();
		$resource->addMeta('foo', 'bar');
		
		$this->expectException(InputException::class);
		
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	public function testClaimUsedResourceIdentifier_BlocksDuplicates(): void {
		$validator = new Validator();
		$resource  = new ResourceObject('foo', 1);
		
		$validator->claimUsedResourceIdentifier($resource);
		
		$this->expectException(DuplicateException::class);
		
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	#[DoesNotPerformAssertions]
	#[DataProvider('dataProviderCheckMemberName_HappyPath')]
	public function testCheckMemberName_HappyPath(string $memberName): void {
		Validator::checkMemberName($memberName);
	}
	
	/**
	 * @return \Iterator<(int | string), array{string}>
	 */
	public static function dataProviderCheckMemberName_HappyPath(): \Iterator {
		yield ['foo'];
		yield ['f_o'];
		yield ['f-o'];
		yield ['42foo'];
		yield ['42'];
	}
	
	#[DataProvider('dataProviderCheckMemberName_InvalidNames')]
	public function testCheckMemberName_InvalidNames(string $memberName): void {
		$this->expectException(InputException::class);
		
		Validator::checkMemberName($memberName);
	}
	
	/**
	 * @return \Iterator<(int | string), array{string}>
	 */
	public static function dataProviderCheckMemberName_InvalidNames(): \Iterator {
		yield ['_'];
		yield ['-'];
		yield ['foo-'];
		yield ['-foo'];
	}
	
	#[DataProvider('dataProviderCheckHttpStatusCode_HappyPath')]
	public function testCheckHttpStatusCode_HappyPath(bool $expectedOutput, int|string $httpStatusCode): void {
		parent::assertSame($expectedOutput, Validator::checkHttpStatusCode($httpStatusCode));
	}
	
	/**
	 * @return \Iterator<(int | string), array{bool, (int | string)}>
	 */
	public static function dataProviderCheckHttpStatusCode_HappyPath(): \Iterator {
		yield [false, 42];
		yield [true,  100];
		yield [true,  200];
		yield [true,  300];
		yield [true,  400];
		yield [true,  500];
		yield [false, 600];
		yield [false, '42'];
		yield [true,  '100'];
	}
}
