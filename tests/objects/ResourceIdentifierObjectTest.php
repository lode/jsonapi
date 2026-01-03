<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

final class ResourceIdentifierObjectTest extends TestCase {
	public function testSetId_HappyPath(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setId('1');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('id', $array);
		parent::assertArrayNotHasKey('lid', $array);
		parent::assertSame('1', $array['id']);
	}
	
	public function testSetId_WithLocalIdAlreadySet(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setLocalId('uuid-1');
		
		$this->expectException(DuplicateException::class);
		
		$resourceIdentifierObject->setId('1');
	}
	
	public function testSetLocalId_HappyPath(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setLocalId('uuid-1');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('lid', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame('uuid-1', $array['lid']);
	}
	
	public function testSetLocalId_WithIdAlreadySet(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setId('1');
		
		$this->expectException(DuplicateException::class);
		
		$resourceIdentifierObject->setLocalId('uuid-1');
	}
	
	public function testFromResourceObject_HappyPath(): void {
		$resource = new ResourceObject('test', 1);
		$resource->addAttribute('foo', 'bar');
		
		$array = $resource->toArray();
		
		parent::assertSame('test', $array['type']);
		parent::assertSame('1', $array['id']);
		parent::assertArrayHasKey('attributes', $array);
		
		$resourceIdentifierObject = ResourceIdentifierObject::fromResourceObject($resource);
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertSame('test', $array['type']);
		parent::assertSame('1', $array['id']);
		parent::assertArrayNotHasKey('attributes', $array);
	}
	
	public function testFromResourceObject_NoFullIdentification(): void {
		$resource = new ResourceObject();
		$resource->toArray();
		
		$this->expectException(InputException::class);
		$this->expectExceptionMessage('resource has no identification yet');
		
		ResourceIdentifierObject::fromResourceObject($resource);
	}
	
	public function testEquals_HappyPath(): void {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject('test', 2);
		$new = new ResourceIdentifierObject('test', 1);
		
		parent::assertFalse($one->equals($two));
		parent::assertTrue($one->equals($new));
	}
	
	public function testEquals_WithoutIdentification(): void {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject();
		
		$this->expectException(Exception::class);
		
		$one->equals($two);
	}
	
	public function testEquals_WithLocalId(): void {
		$one = new ResourceIdentifierObject('test');
		$two = new ResourceIdentifierObject('test');
		$new = new ResourceIdentifierObject('test');
		
		$one->setLocalId('uuid-1');
		$two->setLocalId('uuid-2');
		$new->setLocalId('uuid-1');
		
		parent::assertFalse($one->equals($two));
		parent::assertTrue($one->equals($new));
	}
	
	public function testGetIdentificationKey_HappyPath(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 42);
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayHasKey('id', $array);
		parent::assertArrayNotHasKey('lid', $array);
		parent::assertSame('user', $array['type']);
		parent::assertSame('42', $array['id']);
		parent::assertTrue($resourceIdentifierObject->hasIdentification());
		parent::assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_SetAfterwards(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setType('user');
		
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setId(42);
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayHasKey('id', $array);
		parent::assertSame('user', $array['type']);
		parent::assertSame('42', $array['id']);
		parent::assertTrue($resourceIdentifierObject->hasIdentification());
		parent::assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_WithLocalId(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$resourceIdentifierObject->setType('user');
		$resourceIdentifierObject->setLocalId('uuid-42');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayHasKey('lid', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame('user', $array['type']);
		parent::assertSame('uuid-42', $array['lid']);
		parent::assertTrue($resourceIdentifierObject->hasIdentification());
		parent::assertSame('user|uuid-42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_NoIdentification(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayNotHasKey('type', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame([], $array);
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testGetIdentificationKey_NoFullIdentification(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject('user');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame('user', $array['type']);
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testIsEmpty_IdWithoutType(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setId(42);
		
		parent::assertFalse($resourceIdentifierObject->isEmpty());
	}
	
	public function testIsEmpty_WithAtMembers(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		parent::assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addAtMember('context', 'test');
		
		parent::assertFalse($resourceIdentifierObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		parent::assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addExtensionMember(parent::createStub(ExtensionInterface::class), 'foo', 'bar');
		
		parent::assertFalse($resourceIdentifierObject->isEmpty());
	}
	
	public function testPrimaryId_NoFullIdentification(): void {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$primaryIdMethod = new \ReflectionMethod($resourceIdentifierObject, 'primaryId');
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('resource has no identification yet');
		
		$primaryIdMethod->invoke($resourceIdentifierObject);
	}
}
