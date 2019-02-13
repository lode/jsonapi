<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

interface ResourceInterface {
	/**
	 * @param  boolean $identifierOnly optional, defaults to false
	 * @return ResourceIdentifierObject|ResourceObject
	 */
	public function getResource($identifierOnly=false);
}
