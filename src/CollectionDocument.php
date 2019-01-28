<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class CollectionDocument extends DataDocument {
	/** @var ResourceInterface[] */
	public $resources = [];
	/** @var Validator */
	protected $validator;
	
	public function __construct() {
		parent::__construct();
		
		$this->validator = new Validator();
	}
	
	/**
	 * human api
	 */
	
	/**
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
	 * spec api
	 */
	
	/**
	 * @param ResourceInterface $resource
	 * 
	 * @throws InputException if the resource is empty
	 */
	public function addResource(ResourceInterface $resource) {
		if ($resource->getResource()->isEmpty()) {
			throw new InputException('does not make sense to add empty resources to a collection');
		}
		
		$this->validator->checkUsedResourceIdentifier($resource);
		
		$this->resources[] = $resource;
		
		$this->validator->markUsedResourceIdentifier($resource);
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
}
