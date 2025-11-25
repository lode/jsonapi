<?php

namespace alsvanzelf\jsonapiTests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\ResourceObject;

class ValidatorTest extends TestCase {
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_HappyPath() {
		$validator = new Validator();
		
		$fieldNames      = ['foo'];
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$fieldNames      = ['bar'];
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	public function testClaimUsedFields_EnforceNamespace() {
		$validator  = new Validator();
		$fieldNames = ['foo'];
		
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$this->expectException(DuplicateException::class);
		
		$objectContainer = Validator::OBJECT_CONTAINER_RELATIONSHIPS;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_AllowSameContainer() {
		$validator  = new Validator();
		$fieldNames = ['foo'];
		
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedFields_OptionForReusingTypeField() {
		$validator  = new Validator();
		$fieldNames = ['type'];
		
		$objectContainer = Validator::OBJECT_CONTAINER_TYPE;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$options         = ['enforceTypeFieldNamespace' => false];
		$validator->claimUsedFields($fieldNames, $objectContainer, $options);
	}
	
	#[DoesNotPerformAssertions]
	public function testClearUsedFields_HappyPath() {
		$validator       = new Validator();
		
		$fieldNames      = ['foo'];
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$validator->clearUsedFields($objectContainer);
	}
	
	public function testClearUsedFields_FreesForAnotherNamespace() {
		$validator  = new Validator();
		
		$fieldNames      = ['foo', 'bar'];
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$thrown = false;
		try {
			$fieldNames      = ['bar'];
			$objectContainer = Validator::OBJECT_CONTAINER_RELATIONSHIPS;
			$validator->claimUsedFields($fieldNames, $objectContainer);
		}
		catch (DuplicateException $e) {
			$thrown = true;
		}
		$this->assertTrue($thrown);
		
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->clearUsedFields($objectContainer);
		
		$fieldNames      = ['foo'];
		$objectContainer = Validator::OBJECT_CONTAINER_ATTRIBUTES;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$fieldNames      = ['bar'];
		$objectContainer = Validator::OBJECT_CONTAINER_RELATIONSHIPS;
		$validator->claimUsedFields($fieldNames, $objectContainer);
		
		$this->expectException(DuplicateException::class);
		
		$fieldNames      = ['foo'];
		$objectContainer = Validator::OBJECT_CONTAINER_RELATIONSHIPS;
		$validator->claimUsedFields($fieldNames, $objectContainer);
	}
	
	#[DoesNotPerformAssertions]
	public function testClaimUsedResourceIdentifier_HappyPath() {
		$validator = new Validator();
		
		$resource = new ResourceObject('foo', 1);
		$validator->claimUsedResourceIdentifier($resource);
		
		$resource = new ResourceObject('foo', 2);
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	public function testClaimUsedResourceIdentifier_RequiresIdentification() {
		$validator = new Validator();
		
		$resource = new ResourceObject();
		$resource->addMeta('foo', 'bar');
		
		$this->expectException(InputException::class);
		
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	public function testClaimUsedResourceIdentifier_BlocksDuplicates() {
		$validator = new Validator();
		$resource  = new ResourceObject('foo', 1);
		
		$validator->claimUsedResourceIdentifier($resource);
		
		$this->expectException(DuplicateException::class);
		
		$validator->claimUsedResourceIdentifier($resource);
	}
	
	#[DoesNotPerformAssertions]
	#[DataProvider('dataProviderCheckMemberName_HappyPath')]
	public function testCheckMemberName_HappyPath($memberName) {
		Validator::checkMemberName($memberName);
	}
	
	public static function dataProviderCheckMemberName_HappyPath() {
		return [
			['foo'],
			['f_o'],
			['f-o'],
			['42foo'],
			['42'],
		];
	}
	
	#[DataProvider('dataProviderCheckMemberName_InvalidNames')]
	public function testCheckMemberName_InvalidNames($memberName) {
		$this->expectException(InputException::class);
		
		Validator::checkMemberName($memberName);
	}
	
	public static function dataProviderCheckMemberName_InvalidNames() {
		return [
			['_'],
			['-'],
			['foo-'],
			['-foo'],
		];
	}
	
	#[DataProvider('dataProviderCheckHttpStatusCode_HappyPath')]
	public function testCheckHttpStatusCode_HappyPath($expectedOutput, $httpStatusCode) {
		$this->assertSame($expectedOutput, Validator::checkHttpStatusCode($httpStatusCode));
	}
	
	public static function dataProviderCheckHttpStatusCode_HappyPath() {
		return [
			[false, 42],
			[true,  100],
			[true,  200],
			[true,  300],
			[true,  400],
			[true,  500],
			[false, 600],
			[false, '42'],
			[true,  '100'],
		];
	}
}
