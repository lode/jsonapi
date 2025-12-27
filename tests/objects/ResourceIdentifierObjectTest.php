<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class ResourceIdentifierObjectTest extends TestCase {
	public function testSetId_HappyPath() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setId('1');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('id', $array);
		parent::assertArrayNotHasKey('lid', $array);
		parent::assertSame('1', $array['id']);
	}
	
	public function testSetId_WithLocalIdAlreadySet() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setLocalId('uuid-1');
		
		$this->expectException(DuplicateException::class);
		
		$resourceIdentifierObject->setId('1');
	}
	
	public function testSetLocalId_HappyPath() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setLocalId('uuid-1');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('lid', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame('uuid-1', $array['lid']);
	}
	
	public function testSetLocalId_WithIdAlreadySet() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		$resourceIdentifierObject->setType('test');
		$resourceIdentifierObject->setId('1');
		
		$this->expectException(DuplicateException::class);
		
		$resourceIdentifierObject->setLocalId('uuid-1');
	}
	
	public function testEquals_HappyPath() {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject('test', 2);
		$new = new ResourceIdentifierObject('test', 1);
		
		parent::assertFalse($one->equals($two));
		parent::assertTrue($one->equals($new));
	}
	
	public function testEquals_WithoutIdentification() {
		$one = new ResourceIdentifierObject('test', 1);
		$two = new ResourceIdentifierObject();
		
		$this->expectException(Exception::class);
		
		$one->equals($two);
	}
	
	public function testEquals_WithLocalId() {
		$one = new ResourceIdentifierObject('test');
		$two = new ResourceIdentifierObject('test');
		$new = new ResourceIdentifierObject('test');
		
		$one->setLocalId('uuid-1');
		$two->setLocalId('uuid-2');
		$new->setLocalId('uuid-1');
		
		parent::assertFalse($one->equals($two));
		parent::assertTrue($one->equals($new));
	}
	
	public function testGetIdentificationKey_HappyPath() {
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
	
	public function testGetIdentificationKey_SetAfterwards() {
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
	
	public function testGetIdentificationKey_WithLocalId() {
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
	
	public function testGetIdentificationKey_NoIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayNotHasKey('type', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame([], $array);
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testGetIdentificationKey_NoFullIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject('user');
		
		$array = $resourceIdentifierObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertArrayNotHasKey('id', $array);
		parent::assertSame('user', $array['type']);
		parent::assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testIsEmpty_WithAtMembers() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		parent::assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addAtMember('context', 'test');
		
		parent::assertFalse($resourceIdentifierObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		parent::assertTrue($resourceIdentifierObject->isEmpty());
		
		$resourceIdentifierObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		parent::assertFalse($resourceIdentifierObject->isEmpty());
	}
}
