<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\objects\LinkObject;
use PHPUnit\Framework\TestCase;

final class LinksManagerTest extends TestCase {
	private object $linksManager;
	
	protected function setUp(): void {
		// using LinksManager to make it non-trait to test against it
		$this->linksManager = new class {
			use LinksManager;
			
			/**
			 * @return array<string, string|array{href: string}>
			 */
			public function toArray(): array {
				return $this->links->toArray(); // @phpstan-ignore return.type (toArray() methods don't have explicit array shapes yet)
			}
		};
	}
	
	public function testAddLink_HappyPath(): void {
		$this->linksManager->addLink('foo', 'https://jsonapi.org');
		
		$array = $this->linksManager->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertSame('https://jsonapi.org', $array['foo']);
	}
	
	public function testAddLinkObject_HappyPath(): void {
		$this->linksManager->addLinkObject('foo', new LinkObject('https://jsonapi.org'));
		
		$array = $this->linksManager->toArray();
		
		parent::assertCount(1, $array);
		parent::assertArrayHasKey('foo', $array);
		parent::assertArrayHasKey('href', $array['foo']);
		parent::assertSame('https://jsonapi.org', $array['foo']['href']);
	}
}
