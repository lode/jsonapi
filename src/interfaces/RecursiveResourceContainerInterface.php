<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\ResourceObject;

interface RecursiveResourceContainerInterface {
	/**
	 * gets resources from inside the object recursively
	 * 
	 * this can be used to add resources as included resource
	 * @see ResourceContainerInterface to get only first level resources for adding relationships
	 * 
	 * if the object itself is a resource, this should *not* be returned
	 * @see ResourceInterface to get the resource itself
	 * 
	 * @internal
	 * 
	 * @return ResourceObject[]
	 */
	public function getNestedContainedResourceObjects();
}
