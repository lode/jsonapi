<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\PaginableInterface;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

/**
 * this document is a set of Resources
 * this document should be used if there could be multiple, also if only one or even none is returned
 */
class CollectionDocument extends DataDocument implements PaginableInterface, ResourceContainerInterface {
	/** @var ResourceInterface[] */
	protected array $resources = [];
	/** @var TypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * add resources inside relationships to /included when adding resources to the collection
		 */
		'includeContainedResources' => true,
	];
	
	/**
	 * human api
	 */
	
	/**
	 * generate a CollectionDocument from one or multiple resources
	 * 
	 * adds included resources if found inside the resource's relationships, use {@see ->addResource()} to change that behavior
	 */
	public static function fromResources(ResourceInterface ...$resources): self {
		$collectionDocument = new self();
		
		foreach ($resources as $resource) {
			$collectionDocument->addResource($resource);
		}
		
		return $collectionDocument;
	}
	
	/**
	 * @param array<string, mixed> $attributes if given a ResourceObject is added, otherwise a ResourceIdentifierObject is added
	 */
	public function add(string $type, string|int $id, array $attributes=[]): void {
		if ($attributes === []) {
			$this->addResource(new ResourceIdentifierObject($type, $id));
		}
		else {
			$this->addResource(ResourceObject::fromArray($attributes, $type, $id));
		}
	}
	
	public function setPaginationLinks(
		?string $previousHref=null,
		?string $nextHref=null,
		?string $firstHref=null,
		?string $lastHref=null,
	): void {
		if ($previousHref !== null) {
			$this->addLink('prev', $previousHref);
		}
		if ($nextHref !== null) {
			$this->addLink('next', $nextHref);
		}
		if ($firstHref !== null) {
			$this->addLink('first', $firstHref);
		}
		if ($lastHref !== null) {
			$this->addLink('last', $lastHref);
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * add a resource to the collection
	 * 
	 * adds included resources if found inside the resource's relationships, unless $options['includeContainedResources'] is set to false
	 * 
	 * @param TypeAlias_InternalOptions $options {@see CollectionDocument::$defaults}
	 * 
	 * @throws InputException if the resource is empty
	 */
	public function addResource(ResourceInterface $resource, array $options=[]): void {
		if ($resource->getResource()->isEmpty()) {
			throw new InputException('does not make sense to add empty resources to a collection');
		}
		
		$options = array_merge(self::$defaults, $options);
		
		$this->validator->claimUsedResourceIdentifier($resource);
		
		$this->resources[] = $resource;
		
		if ($options['includeContainedResources'] && $resource instanceof RecursiveResourceContainerInterface) {
			$this->addIncludedResourceObject(...$resource->getNestedContainedResourceObjects());
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray(): array {
		$array = parent::toArray();
		
		$array['data'] = [];
		foreach ($this->resources as $resource) {
			$array['data'][] = $resource->getResource()->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceContainerInterface
	 */
	
	public function getContainedResources(): array {
		return $this->resources;
	}
}
