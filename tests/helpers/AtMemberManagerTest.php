<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitAtMemberManager as AtMemberManager;
use PHPUnit\Framework\TestCase;

class AtMemberManagerTest extends TestCase {
	public function testAddAtMember_HappyPath() {
		$helper = new AtMemberManager();
		
		$this->assertFalse($helper->hasAtMembers());
		$this->assertSame([], $helper->getAtMembers());
		
		$helper->addAtMember('@foo', 'bar');
		
		$array = $helper->getAtMembers();
		
		$this->assertTrue($helper->hasAtMembers());
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('@foo', $array);
		$this->assertSame('bar', $array['@foo']);
	}
	
	public function testAddAtMember_WithoutAtSign() {
		$helper = new AtMemberManager();
		
		$helper->addAtMember('foo', 'bar');
		
		$array = $helper->getAtMembers();
		
		$this->assertArrayHasKey('@foo', $array);
	}
	
	public function testAddAtMember_WithObjectValue() {
		$helper = new AtMemberManager();
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$helper->addAtMember('foo', $object);
		
		$array = $helper->getAtMembers();
		
		$this->assertArrayHasKey('@foo', $array);
		$this->assertArrayHasKey('bar', $array['@foo']);
		$this->assertSame('baz', $array['@foo']['bar']);
	}
	
	public function testAddAtMember_InvalidDoubleAt() {
		$helper = new AtMemberManager();
		
		$this->expectException(InputException::class);
		
		$helper->addAtMember('@@foo', 'bar');
	}
}
