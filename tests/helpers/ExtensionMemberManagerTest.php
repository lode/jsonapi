<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\ExtensionMemberManager;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Extensions
 */
class ExtensionMemberManagerTest extends TestCase {
	private static object $helper;
	
	public static function setUpBeforeClass(): void {
		// using ExtensionMemberManager to make it non-trait to test against it
		self::$helper = new class {
			use ExtensionMemberManager;
		};
	}
	
	public function testAddExtensionMember_HappyPath() {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']);
		
		parent::assertFalse(self::$helper->hasExtensionMembers());
		parent::assertSame([], self::$helper->getExtensionMembers());
		
		self::$helper->addExtensionMember($extension, 'foo', 'bar');
		
		$array = self::$helper->getExtensionMembers();
		
		parent::assertTrue(self::$helper->hasExtensionMembers());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertSame('bar', $array['test:foo']);
	}
	
	public function testAddExtensionMember_WithNamespacePrefixed() {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']);
		
		self::$helper->addExtensionMember($extension, 'test:foo', 'bar');
		
		$array = self::$helper->getExtensionMembers();
		
		parent::assertArrayHasKey('test:foo', $array);
	}
	
	public function testAddExtensionMember_WithObjectValue() {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']);
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		self::$helper->addExtensionMember($extension, 'foo', $object);
		
		$array = self::$helper->getExtensionMembers();
		
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertArrayHasKey('bar', $array['test:foo']);
		parent::assertSame('baz', $array['test:foo']['bar']);
	}
	
	public function testAddExtensionMember_InvalidNamespaceOrCharacter() {
		$extension = parent::createConfiguredStub(ExtensionInterface::class, ['getNamespace' => 'test']);
		
		$this->expectException(InputException::class);
		
		self::$helper->addExtensionMember($extension, 'foo:bar', 'baz');
	}
}
