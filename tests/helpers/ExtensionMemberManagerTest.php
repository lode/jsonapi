<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitExtensionMemberManager as ExtensionMemberManager;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class ExtensionMemberManagerTest extends TestCase {
	protected $extension;
	
	protected function setUp() {
		$this->extension = new TestExtension();
		$this->extension->setNamespace('test');
		$this->extension->setOfficialLink('https://example.org');
	}
	
	public function testAddExtensionMember_HappyPath() {
		$helper = new ExtensionMemberManager();
		
		$this->assertFalse($helper->hasExtensionMembers());
		$this->assertSame([], $helper->getExtensionMembers());
		
		$helper->addExtensionMember($this->extension, 'foo', 'bar');
		
		$array = $helper->getExtensionMembers();
		
		$this->assertTrue($helper->hasExtensionMembers());
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('test:foo', $array);
		$this->assertSame('bar', $array['test:foo']);
	}
	
	public function testAddExtensionMember_WithNamespacePrefixed() {
		$helper = new ExtensionMemberManager();
		
		$helper->addExtensionMember($this->extension, 'test:foo', 'bar');
		
		$array = $helper->getExtensionMembers();
		
		$this->assertArrayHasKey('test:foo', $array);
	}
	
	public function testAddExtensionMember_WithObjectValue() {
		$helper = new ExtensionMemberManager();
		
		$object = new \stdClass();
		$object->bar = 'baz';
		
		$helper->addExtensionMember($this->extension, 'foo', $object);
		
		$array = $helper->getExtensionMembers();
		
		$this->assertArrayHasKey('test:foo', $array);
		$this->assertArrayHasKey('bar', $array['test:foo']);
		$this->assertSame('baz', $array['test:foo']['bar']);
	}
	
	public function testAddExtensionMember_InvalidNamespaceOrCharacter() {
		$helper = new ExtensionMemberManager();
		
		$this->expectException(InputException::class);
		
		$helper->addExtensionMember($this->extension, 'foo:bar', 'baz');
	}
}
