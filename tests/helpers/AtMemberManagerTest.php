<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use PHPUnit\Framework\TestCase;

class AtMemberManagerTest extends TestCase {
	private static object $helper;
	
	public static function setUpBeforeClass(): void {
		// using AtMemberManager to make it non-trait to test against it
		self::$helper = new class {
			use AtMemberManager;
		};
	}
	
	public function testAddAtMember_HappyPath(): void {
		parent::assertFalse(self::$helper->hasAtMembers());
		parent::assertSame([], self::$helper->getAtMembers());
		
		self::$helper->addAtMember('@foo', 'bar');
		
		$array = self::$helper->getAtMembers();
		
		parent::assertTrue(self::$helper->hasAtMembers());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('@foo', $array);
		parent::assertSame('bar', $array['@foo']);
	}
	
	public function testAddAtMember_WithoutAtSign(): void {
		self::$helper->addAtMember('foo', 'bar');
		
		$array = self::$helper->getAtMembers();
		
		parent::assertArrayHasKey('@foo', $array);
	}
	
	public function testAddAtMember_WithObjectValue(): void {
		$object = new \stdClass();
		$object->bar = 'baz';
		
		self::$helper->addAtMember('foo', $object);
		
		$array = self::$helper->getAtMembers();
		
		parent::assertArrayHasKey('@foo', $array);
		parent::assertArrayHasKey('bar', $array['@foo']);
		parent::assertSame('baz', $array['@foo']['bar']);
	}
	
	public function testAddAtMember_InvalidDoubleAt(): void {
		$this->expectException(InputException::class);
		
		self::$helper->addAtMember('@@foo', 'bar');
	}
}
