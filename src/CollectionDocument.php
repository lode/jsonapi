<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class CollectionDocument extends DataDocument implements ResourceContainerInterface {
	/** @var ResourceInterface[] */
	public $resources = [];
	/** @var array */
	private static $defaults = [
		'skipIncluding' => false,
	];
	
	/**
	 * human api
	 */
	
	/**
	 * generate a CollectionDocument from one or multiple resources
	 * 
	 * adds included resources if found inside the resource's relationships, use {@see ->addResource()} to change that behavior
	 * 
	 * @param  ResourceInterface ...$resources
	 * @return CollectionDocument
	 */
	public static function fromResources(ResourceInterface ...$resources) {
		$collectionDocument = new self();
		
		foreach ($resources as $resource) {
			$collectionDocument->addResource($resource);
		}
		
		return $collectionDocument;
	}
	
	/**
	 * @param string     $type
	 * @param string|int $id
	 * @param array      $attributes optional, if given a ResourceObject is added, otherwise a ResourceIdentifierObject is added
	 */
	public function add($type, $id, array $attributes=[]) {
		if ($attributes === []) {
			$this->addResource(new ResourceIdentifierObject($type, $id));
		}
		else {
			$this->addResource(ResourceObject::fromArray($attributes, $type, $id));
		}
	}
	
	/**
	 * set the self link on the document
	 * 
	 * @param string $href
	 * @param array  $meta optional
	 */
	public function setSelfLink($href, array $meta=[]) {
		self::addLink('self', $href, $meta, $level=Document::LEVEL_ROOT);
	}
	
	/**
	 * @param string $previousHref optional
	 * @param string $nextHref     optional
	 * @param string $firstHref    optional
	 * @param string $lastHref     optional
	 */
	public function setPaginationLinks($previousHref=null, $nextHref=null, $firstHref=null, $lastHref=null) {
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
	 * adds included resources if found inside the resource's relationships, unless $options['skipIncluding'] is set to true
	 * 
	 * @param ResourceInterface $resource
	 * @param array             $options  optional {@see CollectionDocument::$defaults}
	 * 
	 * @throws InputException if the resource is empty
	 */
	public function addResource(ResourceInterface $resource, array $options=[]) {
		if ($resource->getResource()->isEmpty()) {
			throw new InputException('does not make sense to add empty resources to a collection');
		}
		
		$options = array_merge(self::$defaults, $options);
		
		$this->validator->claimUsedResourceIdentifier($resource);
		
		$this->resources[] = $resource;
		
		if ($options['skipIncluding'] === false && $resource instanceof RecursiveResourceContainerInterface) {
			$this->addIncludedResourceObject(...$resource->getNestedContainedResourceObjects());
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
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
	
	/**
	 * @inheritDoc
	 */
	public function getContainedResources() {
		return $this->resources;
	}
}
