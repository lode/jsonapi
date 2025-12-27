<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitAtMemberManager as AtMemberManager;
use PHPUnit\Framework\TestCase;

class AtMemberManagerTest extends TestCase {
	public function testAddAtMember_HappyPath() {
		$helper = new AtMemberManager();
		
		parent::assertFalse($helper->hasAtMembers());
		parent::assertSame([], $helper->getAtMembers());
		
		$helper->addAtMember('@foo', 'bar');
		
		$array = $helper->getAtMembers();
		
		parent::assertTrue($helper->hasAtMembers());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('@foo', $array);
		parent::assertSame('bar', $array['@foo']);
	}
	
	public function testAddAtMember_WithoutAtSign() {
		$helper = new AtMemberManager();
		
		$helper->addAtMember('foo', 'bar');
		
		$array = $helper->getAtMembers();
		
		parent::assertArrayHasKey('@foo', $array);
	}
	
	public function testAddAtMember_WithObjectValue() {
		$helper = new AtMemberManager();
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$helper->addAtMember('foo', $object);
		
		$array = $helper->getAtMembers();
		
		parent::assertArrayHasKey('@foo', $array);
		parent::assertArrayHasKey('bar', $array['@foo']);
		parent::assertSame('baz', $array['@foo']['bar']);
	}
	
	public function testAddAtMember_InvalidDoubleAt() {
		$helper = new AtMemberManager();
		
		$this->expectException(InputException::class);
		
		$helper->addAtMember('@@foo', 'bar');
	}
}
