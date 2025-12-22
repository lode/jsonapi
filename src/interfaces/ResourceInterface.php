<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

interface ResourceInterface {
	/**
	 * @internal
	 * 
	 * @return ($identifierOnly is true ? ResourceIdentifierObject : ResourceObject)
	 */
	public function getResource(bool $identifierOnly=false): ResourceIdentifierObject|ResourceObject;
}
