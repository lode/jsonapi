<?php

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ExampleVersionProfile implements ProfileInterface {
	public function getOfficialLink() {
		return 'https://jsonapi.org/format/1.1/#profile-keywords';
	}
	
	/**
	 * optionally helpers for the specific profile
	 */
	
	public function setVersion(ResourceInterface $resource, $version) {
		if ($resource instanceof ResourceDocument) {
			$resource->addMeta('version', $version, $level=Document::LEVEL_RESOURCE);
		}
		else {
			$resource->addMeta('version', $version);
		}
	}
}
