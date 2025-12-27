<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitExtensionMemberManager as ExtensionMemberManager;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

/**
 * @group Extensions
 */
class ExtensionMemberManagerTest extends TestCase {
	public function testAddExtensionMember_HappyPath() {
		$helper    = new ExtensionMemberManager();
		$extension = new TestExtension();
		$extension->setNamespace('test');
		
		parent::assertFalse($helper->hasExtensionMembers());
		parent::assertSame([], $helper->getExtensionMembers());
		
		$helper->addExtensionMember($extension, 'foo', 'bar');
		
		$array = $helper->getExtensionMembers();
		
		parent::assertTrue($helper->hasExtensionMembers());
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertSame('bar', $array['test:foo']);
	}
	
	public function testAddExtensionMember_WithNamespacePrefixed() {
		$helper    = new ExtensionMemberManager();
		$extension = new TestExtension();
		$extension->setNamespace('test');
		
		$helper->addExtensionMember($extension, 'test:foo', 'bar');
		
		$array = $helper->getExtensionMembers();
		
		parent::assertArrayHasKey('test:foo', $array);
	}
	
	public function testAddExtensionMember_WithObjectValue() {
		$helper    = new ExtensionMemberManager();
		$extension = new TestExtension();
		$extension->setNamespace('test');
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$helper->addExtensionMember($extension, 'foo', $object);
		
		$array = $helper->getExtensionMembers();
		
		parent::assertArrayHasKey('test:foo', $array);
		parent::assertArrayHasKey('bar', $array['test:foo']);
		parent::assertSame('baz', $array['test:foo']['bar']);
	}
	
	public function testAddExtensionMember_InvalidNamespaceOrCharacter() {
		$helper    = new ExtensionMemberManager();
		$extension = new TestExtension();
		$extension->setNamespace('test');
		
		$this->expectException(InputException::class);
		
		$helper->addExtensionMember($extension, 'foo:bar', 'baz');
	}
}
