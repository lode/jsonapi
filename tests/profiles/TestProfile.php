<?php

namespace alsvanzelf\jsonapiTests\profiles;

use alsvanzelf\jsonapi\interfaces\ProfileInterface;

class TestProfile implements ProfileInterface {
	private $aliasedLink;
	
	public function setAliasedLink($aliasedLink) {
		$this->aliasedLink = $aliasedLink;
	}
	
	public function __construct(array $aliases=[]) {}
	public function getKeyword($keyword) {}
	public function getOfficialKeywords() {}
	public function getOfficialLink() {}
	public function getAliasedLink() {
		return $this->aliasedLink;
	}
}
