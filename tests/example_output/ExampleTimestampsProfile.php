<?php

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ExampleTimestampsProfile implements ProfileInterface {
	public function getOfficialLink() {
		return 'https://jsonapi.org/recommendations/#authoring-profiles';
	}
	
	public function setTimestamps(ResourceInterface $resource, ?\DateTimeInterface $created=null, ?\DateTimeInterface $updated=null) {
		if ($resource instanceof ResourceIdentifierObject) {
			throw new Exception('cannot add attributes to identifier objects');
		}
		
		$timestamps = [];
		if ($created !== null) {
			$timestamps['created'] = $created->format(\DateTime::ISO8601);
		}
		if ($updated !== null) {
			$timestamps['updated'] = $updated->format(\DateTime::ISO8601);
		}
		
		$resource->add('timestamps', $timestamps);
	}
}
