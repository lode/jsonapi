<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitAtMembers as AtMembers;
use PHPUnit\Framework\TestCase;

class AtMembersTest extends TestCase {
	public function testAddAtMember_HappyPath() {
		$helper = new AtMembers();
		
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
		$helper = new AtMembers();
		
		$helper->addAtMember('foo', 'bar');
		
		$array = $helper->getAtMembers();
		
		$this->assertArrayHasKey('@foo', $array);
	}
	
	public function testAddAtMember_WithObjectValue() {
		$helper = new AtMembers();
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$helper->addAtMember('foo', $object);
		
		$array = $helper->getAtMembers();
		
		$this->assertArrayHasKey('@foo', $array);
		$this->assertArrayHasKey('bar', $array['@foo']);
		$this->assertSame('baz', $array['@foo']['bar']);
	}
	
	public function testAddAtMember_InvalidDoubleAt() {
		$helper = new AtMembers();
		
		$this->expectException(InputException::class);
		
		$helper->addAtMember('@@foo', 'bar');
	}
}
