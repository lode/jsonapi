<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

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
	 * @return ResourceInterface[]
	 */
	public function getContainedResources(): array;
}
