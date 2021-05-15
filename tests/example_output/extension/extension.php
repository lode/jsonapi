<?php

namespace alsvanzelf\jsonapiTests\example_output\extension;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleVersionExtension;

class extension {
	public static function createJsonapiDocument() {
		$extension = new ExampleVersionExtension();
		
		$document = new ResourceDocument('user', 42);
		$document->applyExtension($extension);
		
		$extension->setVersion($document, '2019');
		
		return $document;
	}
}
