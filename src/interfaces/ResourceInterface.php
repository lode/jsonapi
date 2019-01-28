<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

interface ResourceInterface {
	/**
	 * @return ResourceIdentifierObject|ResourceObject
	 */
	public function getResource($identifierOnly=false);
}
