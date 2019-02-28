<?php

namespace alsvanzelf\jsonapiTests\example_output\example_profile;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleVersionProfile;

class example_profile {
	public static function createJsonapiDocument() {
		$profile = new ExampleVersionProfile(['version' => 'ref']);
		
		$document = new ResourceDocument('user', 42);
		$document->applyProfile($profile);
		
		$profile->setVersion($document, '2019');
		
		return $document;
	}
}
