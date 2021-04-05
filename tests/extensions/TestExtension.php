<?php

namespace alsvanzelf\jsonapiTests\extensions;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

class TestExtension implements ExtensionInterface {
	private $namespace;
	private $officialLink;
	
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}
	
	public function setOfficialLink($officialLink) {
		$this->officialLink = $officialLink;
	}
	
	public function getNamespace() {
		return $this->namespace;
	}
	
	public function getOfficialLink() {
		return $this->officialLink;
	}
}
