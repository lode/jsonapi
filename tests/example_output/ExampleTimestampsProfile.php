<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\HasAttributesInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ExampleTimestampsProfile implements ProfileInterface {
	public function getOfficialLink(): string {
		return 'https://jsonapi.org/recommendations/#authoring-profiles';
	}
	
	public function setTimestamps(
		ResourceInterface & HasAttributesInterface $resource,
		?\DateTimeInterface $created=null,
		?\DateTimeInterface $updated=null,
	): void {
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
