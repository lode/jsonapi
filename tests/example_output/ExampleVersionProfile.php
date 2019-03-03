<?php

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\helpers\ProfileAliasManager;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ExampleVersionProfile extends ProfileAliasManager implements ProfileInterface {
	/**
	 * the required methods (next to extending ProfileAliasManager)
	 */
	
	public function getOfficialLink() {
		return 'https://jsonapi.org/format/1.1/#profile-keywords-and-aliases';
	}
	
	public function getOfficialKeywords() {
		return ['version'];
	}
	
	/**
	 * optionally helpers for the specific profile
	 */
	
	public function setVersion(ResourceInterface $resource, $version) {
		if ($resource instanceof ResourceDocument) {
			$resource->addMeta($this->getKeyword('version'), $version, $level=Document::LEVEL_RESOURCE);
		}
		else {
			$resource->addMeta($this->getKeyword('version'), $version);
		}
	}
}
