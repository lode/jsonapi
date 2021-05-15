<?php

namespace alsvanzelf\jsonapiTests\profiles;

use alsvanzelf\jsonapi\interfaces\ProfileInterface;

class TestProfile implements ProfileInterface {
	private $officialLink;
	
	public function setOfficialLink($officialLink) {
		$this->officialLink = $officialLink;
	}
	
	public function getOfficialLink() {
		return $this->officialLink;
	}
}
