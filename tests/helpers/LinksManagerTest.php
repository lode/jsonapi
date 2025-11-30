<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\objects\LinkObject;
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
	
	public function testAddLinkObject_HappyPath() {
		$linksManager = new LinksManager();
		$linksManager->addLinkObject('foo', new LinkObject('https://jsonapi.org'));
		
		$array = $linksManager->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey('foo', $array);
		$this->assertArrayHasKey('href', $array['foo']);
		$this->assertSame('https://jsonapi.org', $array['foo']['href']);
	}
}
