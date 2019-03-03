<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\ProfileAliasManager;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;

/**
 * using ProfileAliasManager to make it non-abstract to test against it
 */
class TestableNonAbstractProfileAliasManager extends ProfileAliasManager implements ProfileInterface {
	public function getAliasMapping() {
		$aliasMapping = new \ReflectionProperty(ProfileAliasManager::class, 'aliasMapping');
		$aliasMapping->setAccessible(true);
		
		return $aliasMapping->getValue($this);
	}
	
	public function getKeywordMapping() {
		$keywordMapping = new \ReflectionProperty(ProfileAliasManager::class, 'keywordMapping');
		$keywordMapping->setAccessible(true);
		
		return $keywordMapping->getValue($this);
	}
	
	public function getOfficialKeywords() {
		return ['foo', 'bar'];
	}
	
	public function getOfficialLink() {
		return 'https://jsonapi.org';
	}
}

class TestableNonAbstractProfileAliasManager_WithoutKeywords extends TestableNonAbstractProfileAliasManager {
	public function getOfficialKeywords() {
		return [];
	}
}
