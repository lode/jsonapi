<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapiTests\extensions\TestExtension;
use PHPUnit\Framework\TestCase;

class LinkObjectTest extends TestCase {
	public function testSetDescribedBy_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->setDescribedBy('https://jsonapi.org');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('describedby', $array);
		$this->assertArrayHasKey('href', $array['describedby']);
		$this->assertSame('https://jsonapi.org', $array['describedby']['href']);
	}
	
	public function testAddLanguage_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->addLanguage('nl-NL');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('hreflang', $array);
		$this->assertSame('nl-NL', $array['hreflang']);
	}
	
	public function testAddLanguage_Multiple() {
		$linkObject = new LinkObject();
		
		$linkObject->addLanguage('nl-NL');
		$array = $linkObject->toArray();
		$this->assertSame('nl-NL', $array['hreflang']);
		
		$linkObject->addLanguage('en-US');
		$array = $linkObject->toArray();
		$this->assertSame(['nl-NL', 'en-US'], $array['hreflang']);
	}
	
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
	
	public function testSetRelationType_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->setRelationType('external');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('rel', $array);
		$this->assertSame('external', $array['rel']);
	}
	
	public function testSetDescribedByLinkObject_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$describedBy = new LinkObject('https://jsonapi.org');
		$linkObject->setDescribedByLinkObject($describedBy);
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('describedby', $array);
		$this->assertArrayHasKey('href', $array['describedby']);
		$this->assertSame('https://jsonapi.org', $array['describedby']['href']);
	}
	
	public function testSetHumanTitle_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->setHumanTitle('A link');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('title', $array);
		$this->assertSame('A link', $array['title']);
	}
	
	public function testSetMediaType_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->setMediaType('text/html');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('type', $array);
		$this->assertSame('text/html', $array['type']);
	}
	
	public function testSetHreflang_HappyPath() {
		$linkObject = new LinkObject();
		
		$this->assertTrue($linkObject->isEmpty());
		
		$linkObject->setHreflang('nl-NL', 'en-US');
		
		$this->assertFalse($linkObject->isEmpty());
		
		$array = $linkObject->toArray();
		
		$this->assertArrayHasKey('hreflang', $array);
		$this->assertSame(['nl-NL', 'en-US'], $array['hreflang']);
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
