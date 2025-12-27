<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use alsvanzelf\jsonapiTests\profiles\TestProfile;
use PHPUnit\Framework\TestCase;

class JsonapiObjectTest extends TestCase {
	public function testAddMeta_HappyPath() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		parent::assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addMeta('foo', 'bar');
		
		parent::assertFalse($jsonapiObject->isEmpty());
		
		$array = $jsonapiObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testIsEmpty_WithAtMembers() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		parent::assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addAtMember('context', 'test');
		
		parent::assertFalse($jsonapiObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionLink() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		parent::assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addExtension(new TestExtension());
		
		parent::assertFalse($jsonapiObject->isEmpty());
	}
	
	/**
	 * @group Profiles
	 */
	public function testIsEmpty_WithProfileLink() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		parent::assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addProfile(new TestProfile());
		
		parent::assertFalse($jsonapiObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers() {
		$jsonapiObject = new JsonapiObject($version=null);
		
		parent::assertTrue($jsonapiObject->isEmpty());
		
		$jsonapiObject->addExtensionMember(new TestExtension(), 'foo', 'bar');
		
		parent::assertFalse($jsonapiObject->isEmpty());
	}
}
