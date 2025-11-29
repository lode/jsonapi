<?php

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\HasAttributesInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ExampleTimestampsProfile implements ProfileInterface {
	public function getOfficialLink() {
		return 'https://jsonapi.org/recommendations/#authoring-profiles';
	}
	
	/**
	 * @param ResourceInterface&HasAttributesInterface $resource
	 */
	public function setTimestamps(ResourceInterface $resource, ?\DateTimeInterface $created=null, ?\DateTimeInterface $updated=null) {
		if ($resource instanceof HasAttributesInterface === false) {
			throw new InputException('cannot add attributes to identifier objects');
		}
		
		$timestamps = [];
		if ($created !== null) {
			$timestamps['created'] = $created->format(\DateTime::ISO8601);
		}
		if ($updated !== null) {
			$timestamps['updated'] = $updated->format(\DateTime::ISO8601);
		}
		
		$resource->addAttribute('timestamps', $timestamps);
	}
}
