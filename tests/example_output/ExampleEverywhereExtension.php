<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

class ExampleEverywhereExtension implements ExtensionInterface {
	public function getOfficialLink() {
		return 'https://example.org/everywhere-extension';
	}
	
	public function getNamespace() {
		return 'everywhere';
	}
}
