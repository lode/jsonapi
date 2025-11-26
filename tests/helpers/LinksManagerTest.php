<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;
use alsvanzelf\jsonapiTests\helpers\TestableNonTraitLinksManager as LinksManager;
use PHPUnit\Framework\TestCase;

class LinksManagerTest extends TestCase {
	public function testAddLink_HappyPath() {
		$linksManager = new LinksManager();
		$linksManager->addLink('foo', 'https://jsonapi.org');
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertSame('https://jsonapi.org', $array['foo']);
	}
	
	public function testAppendLink_HappyPath() {
		$linksManager = new LinksManager();
		$linksManager->appendLink('foo', 'https://jsonapi.org'); // @phpstan-ignore method.deprecated
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]);
	}
	
	public function testAppendLink_WithMeta() {
		$linksManager = new LinksManager();
		$linksManager->appendLink('foo', 'https://jsonapi.org', ['bar' => 'baz']); // @phpstan-ignore method.deprecated
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertArrayHasKey('href', $array['foo'][0]);
		$this->assertArrayHasKey('meta', $array['foo'][0]);
		$this->assertArrayHasKey('bar', $array['foo'][0]['meta']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]['href']);
		$this->assertSame('baz', $array['foo'][0]['meta']['bar']);
	}
	
	public function testAppendLink_MultipleLinks() {
		$linksManager = new LinksManager();
		$linksManager->appendLink('foo', 'https://jsonapi.org', ['bar' => 'baz']); // @phpstan-ignore method.deprecated
		$linksManager->appendLink('foo', 'https://jsonapi.org/2'); // @phpstan-ignore method.deprecated
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(2, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertArrayHasKey(1, $array['foo']);
		$this->assertArrayHasKey('href', $array['foo'][0]);
		$this->assertArrayHasKey('meta', $array['foo'][0]);
		$this->assertArrayHasKey('bar', $array['foo'][0]['meta']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]['href']);
		$this->assertSame('baz', $array['foo'][0]['meta']['bar']);
		$this->assertSame('https://jsonapi.org/2', $array['foo'][1]);
	}
	
	public function testAddLinkObject_HappyPath() {
		$linksManager = new LinksManager();
		$linksManager->addLinkObject('foo', new LinkObject('https://jsonapi.org'));
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('href', $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo']['href']);
	}
	
	/**
	 * @deprecated array links are not supported anymore
	 */
	public function testAddLinksArray_HappyPath() {
		$linksArray = new LinksArray();
		$linksArray->add('https://jsonapi.org');
		
		$linksManager = new LinksManager();
		$linksManager->addLinksArray('foo', $linksArray);
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertCount(1, $array['foo']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo'][0]);
	}
	
	/**
	 * @deprecated array links are not supported anymore
	 */
	public function testAppendLinkObject_HappyPath() {
		$linksManager = new LinksManager();
		$linksManager->addLinksArray('foo', LinksArray::fromArray(['https://jsonapi.org/1']));
		$linksManager->appendLinkObject('foo', new LinkObject('https://jsonapi.org/2'));
		$linksManager->appendLinkObject('foo', new LinkObject('https://jsonapi.org/3'));
		$linksManager->appendLinkObject('bar', new LinkObject('https://jsonapi.org/4'));
		
		$array = $linksManager->toArray();
		
		$this->assertCount(2, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('bar', $array);
		$this->assertCount(3, $array['foo']);
		$this->assertCount(1, $array['bar']);
		$this->assertArrayHasKey(0, $array['foo']);
		$this->assertArrayHasKey(1, $array['foo']);
		$this->assertArrayHasKey(2, $array['foo']);
		$this->assertArrayHasKey(0, $array['bar']);
		$this->assertArrayHasKey('href', $array['foo'][1]);
		$this->assertArrayHasKey('href', $array['foo'][2]);
		$this->assertArrayHasKey('href', $array['bar'][0]);
		$this->assertSame('https://jsonapi.org/1', $array['foo'][0]);
		$this->assertSame('https://jsonapi.org/2', $array['foo'][1]['href']);
		$this->assertSame('https://jsonapi.org/3', $array['foo'][2]['href']);
		$this->assertSame('https://jsonapi.org/4', $array['bar'][0]['href']);
	}
}
