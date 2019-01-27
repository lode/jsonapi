<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class CollectionDocument extends DataDocument {
	public $resources = [];
	private $resourceIdentifiers = [];
	
	/**
	 * human api
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
	
	public function addResource(ResourceInterface $resource) {
		$this->checkUsedResourceIdentifier($resource);
		
		$this->resources[] = $resource;
		
		$this->markUsedResourceIdentifier($resource);
	}
	
	/**
	 * output
	 */
	
	public function toArray() {
		$array = parent::toArray();
		
		$array['data'] = [];
		foreach ($this->resources as $resource) {
			$array['data'][] = get_object_vars($resource->getResource());
		}
		
		return $array;
	}
	
	/**
	 * internal api
	 */
	
	private function checkUsedResourceIdentifier(ResourceInterface $resource) {
		$resourceKey = $resource->getResource()->type.'|'.$resource->getResource()->id;
		if (isset($this->resourceIdentifiers[$resourceKey]) === false) {
			return;
		}
		
		throw new DuplicateException('can not have multiple resources with the same identification');
	}
	
	private function markUsedResourceIdentifier(ResourceInterface $resource) {
		$resourceKey = $resource->getResource()->type.'|'.$resource->getResource()->id;
		$this->resourceIdentifiers[$resourceKey] = true;
	}
}
