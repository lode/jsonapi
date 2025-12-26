<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\extensions;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

class TestExtension implements ExtensionInterface {
	private string $namespace = '';
	private string $officialLink = '';
	
	public function setNamespace(string $namespace) {
		$this->namespace = $namespace;
	}
	
	public function setOfficialLink(string $officialLink) {
		$this->officialLink = $officialLink;
	}
	
	public function getNamespace(): string {
		return $this->namespace;
	}
	
	public function getOfficialLink(): string {
		return $this->officialLink;
	}
}
