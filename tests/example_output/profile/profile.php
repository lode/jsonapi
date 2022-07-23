<?php

namespace alsvanzelf\jsonapiTests\example_output\profile;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleTimestampsProfile;

class profile {
	public static function createJsonapiDocument() {
		$profile = new ExampleTimestampsProfile();
		
		$document = new ResourceDocument('user', 42);
		$document->applyProfile($profile);
		
		$profile->setTimestamps($document, new \DateTime('2019-01-01T00:00:00+0000'), new \DateTime('2021-01-01T00:00:00+0000'));
		
		return $document;
	}
}
