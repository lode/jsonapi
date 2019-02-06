<?php

use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use PHPUnit\Framework\TestCase;

class ResourceIdentifierObjectTest extends TestCase {
	public function testGetIdentificationKey_HappyPath() {
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 42);
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertSame('42', $array['id']);
		$this->assertTrue($resourceIdentifierObject->hasIdentification());
		$this->assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_SetAfterwards() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setType('user');
		
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$resourceIdentifierObject->setId(42);
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertSame('42', $array['id']);
		$this->assertTrue($resourceIdentifierObject->hasIdentification());
		$this->assertSame('user|42', $resourceIdentifierObject->getIdentificationKey());
	}
	
	public function testGetIdentificationKey_NoIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject();
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertNull($array['type']);
		$this->assertNull($array['id']);
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
	
	public function testGetIdentificationKey_NoFullIdentification() {
		$resourceIdentifierObject = new ResourceIdentifierObject('user');
		
		$array = $resourceIdentifierObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('id', $array);
		$this->assertSame('user', $array['type']);
		$this->assertNull($array['id']);
		$this->assertFalse($resourceIdentifierObject->hasIdentification());
		
		$this->expectException(Exception::class);
		
		$resourceIdentifierObject->getIdentificationKey();
	}
}
