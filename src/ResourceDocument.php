<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\ResourceObject;

class ResourceDocument extends DataDocument implements ResourceInterface {
	private $resource;
	
	/**
	 * @note $type and $id are optional to pass during construction
	 *       however they are required for a valid ResourceDocument
	 *       so use ->setResource() if not passing them during construction
	 * 
	 * @param string     $type optional
	 * @param string|int $id   optional
	 */
	public function __construct($type=null, $id=null) {
		parent::__construct();
		
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
	public function add($key, $value) {
		$this->resource->add($key, $value);
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::META_LEVEL_* constants, optional, defaults to Document::META_LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is unknown
	 */
	public function addMeta($key, $value, $level=Document::META_LEVEL_ROOT) {
		if ($level === Document::META_LEVEL_ROOT || $level === Document::META_LEVEL_JSONAPI) {
			parent::addMeta($key, $value, $level);
		}
		elseif ($level === Document::META_LEVEL_RESOURCE) {
			$this->resource->addMeta($key, $value);
		}
		else {
			throw new InputException('unknown meta level "'.$level.'"');
		}
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
		
		$array['data'] = null;
		if ($this->resource !== null && $this->resource->isEmpty() === false) {
			$array['data'] = $this->resource->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource() {
		return $this->resource;
	}
}
