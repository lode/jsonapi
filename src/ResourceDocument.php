<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceObject;

class ResourceDocument extends DataDocument implements ResourceInterface {
	private $resource;
	
	public function __construct($type, $id) {
		// ensure the human api methods have a resource object to work with
		$this->setResource(new ResourceObject($type, $id));
	}
	
	/**
	 * human api
	 */
	
	/**
	 * add key-value pairs to the resource's attributes
	 * 
	 * @param string $key
	 * @param mixed  $value objects will be converted using `get_object_vars()`
	 */
	public function addData($key, $value) {
		$this->resource->addData($key, $value);
	}
	
	public function setData(array $array) {
		$this->resource->setData($array);
	}
	
	/**
	 * spec api
	 */
	
	public function setResource(ResourceInterface $resource) {
		if ($resource instanceof ResourceDocument) {
			throw new InputException('does not make sense to set a document inside a document, use ResourceObject or ResourceIdentifierObject instead');
		}
		
		$this->resource = $resource;
	}
	
	/**
	 * output
	 */
	
	public function toArray() {
		$array = parent::toArray();
		
		$array['data'] = [
			'type'       => $this->resource->type,
			'id'         => $this->resource->id,
			'attributes' => $this->resource->attributes,
		];
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource() {
		return $this->resource;
	}
}
