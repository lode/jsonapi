<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class LinkObjectTest extends TestCase {
	public function testAddMeta_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->addMeta('foo', 'bar');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('foo', $array['meta']);
		$this->assertSame('bar', $array['meta']['foo']);
	}
	
	public function testIsEmpty_WithAtMembers() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->addAtMember('context', 'test');
		
		$this->assertFalse($linkObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		$this->assertFalse($linkObject->isEmpty());
	}
}
