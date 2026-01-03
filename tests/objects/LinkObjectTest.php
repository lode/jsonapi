<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use PHPUnit\Framework\TestCase;

final class LinkObjectTest extends TestCase {
	public function testSetDescribedBy_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->setDescribedBy('https://jsonapi.org');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('describedby', $array);
		parent::assertArrayHasKey('href', $array['describedby']);
		parent::assertSame('https://jsonapi.org', $array['describedby']['href']);
	}
	
	public function testAddLanguage_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->addLanguage('nl-NL');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('hreflang', $array);
		parent::assertSame('nl-NL', $array['hreflang']);
	}
	
	public function testAddLanguage_Multiple(): void {
		$linkObject = new LinkObject();
		
		$linkObject->addLanguage('nl-NL');
		$array = $linkObject->toArray();
		parent::assertSame('nl-NL', $array['hreflang']);
		
		$linkObject->addLanguage('en-US');
		$array = $linkObject->toArray();
		parent::assertSame(['nl-NL', 'en-US'], $array['hreflang']);
	}
	
	public function testAddMeta_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->addMeta('foo', 'bar');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('foo', $array['meta']);
		parent::assertSame('bar', $array['meta']['foo']);
	}
	
	public function testSetRelationType_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->setRelationType('external');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('rel', $array);
		parent::assertSame('external', $array['rel']);
	}
	
	public function testSetDescribedByLinkObject_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$describedBy = new LinkObject('https://jsonapi.org');
		$linkObject->setDescribedByLinkObject($describedBy);
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('describedby', $array);
		parent::assertArrayHasKey('href', $array['describedby']);
		parent::assertSame('https://jsonapi.org', $array['describedby']['href']);
	}
	
	public function testSetHumanTitle_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->setHumanTitle('A link');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('title', $array);
		parent::assertSame('A link', $array['title']);
	}
	
	public function testSetMediaType_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->setMediaType('text/html');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('type', $array);
		parent::assertSame('text/html', $array['type']);
	}
	
	public function testSetHreflang_HappyPath(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->setHreflang('nl-NL', 'en-US');
		
		parent::assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		parent::assertArrayHasKey('hreflang', $array);
		parent::assertSame(['nl-NL', 'en-US'], $array['hreflang']);
	}
	
	public function testIsEmpty_WithAtMembers(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->addAtMember('context', 'test');
		
		parent::assertFalse($linkObject->isEmpty());
	}
	
	/**
	 * @group Extensions
	 */
	public function testIsEmpty_WithExtensionMembers(): void {
		$linkObject = new LinkObject();
		
		parent::assertTrue($linkObject->isEmpty());
		
		$linkObject->addExtensionMember(parent::createStub(ExtensionInterface::class), 'foo', 'bar');
		
		parent::assertFalse($linkObject->isEmpty());
	}
}
