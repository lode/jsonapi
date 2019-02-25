<?php

namespace alsvanzelf\jsonapiTests\example_output\profiles;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleVersionProfile;

class profiles {
	public static function createJsonapiDocument() {
		$profile = new ExampleVersionProfile(['version' => 'ref']);
		
		$document = new ResourceDocument('user', 42);
		$document->applyProfile($profile);
		
		$profile->setVersion($document, '2019');
		
		return $document;
	}
}
