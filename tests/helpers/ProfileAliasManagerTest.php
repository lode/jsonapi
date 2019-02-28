<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ProfileLinkObject;
use alsvanzelf\jsonapiTests\helpers\TestableNonAbstractProfileAliasManager as ProfileAliasManager;
use alsvanzelf\jsonapiTests\helpers\TestableNonAbstractProfileAliasManager_WithoutKeywords as ProfileAliasManager_WithoutKeywords;
use PHPUnit\Framework\TestCase;

class ProfileAliasManagerTest extends TestCase {
	public function testConstructor_HappyPath() {
		$profileAliasManager = new ProfileAliasManager();
		
		$this->assertSame([], $profileAliasManager->getAliasMapping());
		$this->assertSame(['foo' => 'foo', 'bar' => 'bar'], $profileAliasManager->getKeywordMapping());
	}
	
	public function testConstructor_WithAliases() {
		$profileAliasManager = new ProfileAliasManager(['bar' => 'baz']);
		
		$this->assertSame(['bar' => 'baz'], $profileAliasManager->getAliasMapping());
		$this->assertSame(['foo' => 'foo', 'bar' => 'baz'], $profileAliasManager->getKeywordMapping());
	}
	
	public function testConstructor_WithoutOfficialKeywords() {
		$profileAliasManager = new ProfileAliasManager_WithoutKeywords();
		
		$this->assertSame([], $profileAliasManager->getAliasMapping());
		$this->assertSame([], $profileAliasManager->getKeywordMapping());
	}
	
	public function testConstructor_NonAdjustedAliases() {
		$this->expectException(InputException::class);
		
		new ProfileAliasManager(['foo' => 'foo']);
	}
	
	public function testConstructor_NonExistingKeyword() {
		$this->expectException(InputException::class);
		
		new ProfileAliasManager(['baz' => 'bar']);
	}
	
	public function testGetKeyword_HappyPath() {
		$profileAliasManager = new ProfileAliasManager(['bar' => 'baz']);
		
		$this->assertSame('foo', $profileAliasManager->getKeyword('foo'));
		$this->assertSame('baz', $profileAliasManager->getKeyword('bar'));
	}
	
	public function testGetKeyword_NonExistingKeyword() {
		$profileAliasManager = new ProfileAliasManager();
		
		$this->expectException(InputException::class);
		
		$profileAliasManager->getKeyword('baz');
	}
	
	public function testGetAliasedLink_HappyPath() {
		$profileAliasManager = new ProfileAliasManager();
		
		if (method_exists($this, 'assertIsString')) {
			$this->assertIsString($profileAliasManager->getAliasedLink());
		}
		else {
			$this->assertInternalType('string', $profileAliasManager->getAliasedLink());
		}
		$this->assertSame('https://jsonapi.org', $profileAliasManager->getAliasedLink());
	}
	
	public function testGetAliasedLink_ObjectWithAliases() {
		$profileAliasManager = new ProfileAliasManager(['bar' => 'baz']);
		
		$this->assertInstanceOf(ProfileLinkObject::class, $profileAliasManager->getAliasedLink());
		
		$array = $profileAliasManager->getAliasedLink()->toArray();
		
		$this->assertArrayHasKey('href', $array);
		$this->assertSame('https://jsonapi.org', $array['href']);
		
		$this->assertArrayHasKey('aliases', $array);
		$this->assertCount(1, $array['aliases']);
		$this->assertArrayHasKey('bar', $array['aliases']);
		$this->assertSame('baz', $array['aliases']['bar']);
	}
}
