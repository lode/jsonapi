<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

interface ResourceContainerInterface {
	/**
	 * gets resources from inside the object, from first level only
	 * 
	 * this can be used to add resources as relationships
	 * @see RecursiveResourceContainerInterface to get nested resources as well for adding as included resources
	 * 
	 * if the object itself is a resource, this should *not* be returned
	 * @see ResourceInterface to get the resource itself
	 * 
	 * @internal
	 * 
	 * @return array with a mix of ResourceIdentifierObject and ResourceObject
	 */
	public function getContainedResources();
}
