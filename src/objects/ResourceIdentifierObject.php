<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\Exception;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class ResourceIdentifierObject implements ObjectInterface, ResourceInterface {
	/** @var string */
	public $type;
	/** @var string */
	public $id;
	/** @var MetaObject */
	public $meta;
	/** @var Validator */
	protected $validator;
	
	/**
	 * @note $type and $id are optional to pass during construction
	 *       however they are required for a valid ResourceIdentifierObject
	 *       so use ->setType() and ->setId() if not passing them during construction
	 * 
	 * @param string     $type optional
	 * @param string|int $id   optional
	 */
	public function __construct($type=null, $id=null) {
		$this->validator = new Validator();
		
		if ($type !== null) {
			$this->setType($type);
		}
		if ($id !== null) {
			$this->setId($id);
		}
		
		// always mark as used, as these keys are reserved
		$this->validator->claimUsedFields($fieldNames=['type'], Validator::OBJECT_CONTAINER_TYPE);
		$this->validator->claimUsedFields($fieldNames=['id'], Validator::OBJECT_CONTAINER_ID);
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param  ResourceObject $resourceObject
	 * @return ResourceIdentifierObject
	 */
	public static function fromResourceObject(ResourceObject $resourceObject) {
		$resourceIdentifierObject = new self($resourceObject->type, $resourceObject->id);
		
		if ($resourceObject->meta !== null) {
			$resourceIdentifierObject->setMetaObject($resourceObject->meta);
		}
		
		return $resourceIdentifierObject;
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addMeta($key, $value) {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * @param  ResourceInterface $resource
	 * @return boolean
	 */
	public function equals(ResourceInterface $resource) {
		return ($this->type === $resource->type && $this->id === $resource->id);
	}
	
	/**
	 * @return boolean
	 */
	public function hasIdentification() {
		return ($this->type !== null && $this->id !== null);
	}
	
	/**
	 * get a key to uniquely define this resource
	 * 
	 * @return string
	 * 
	 * @throws Exception if type or id is not set yet
	 */
	public function getIdentificationKey() {
		if ($this->hasIdentification() === false) {
			throw new Exception('resource has no identification yet');
		}
		
		return $this->type.'|'.$this->id;
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * @param string|int $id will be casted to a string
	 */
	public function setId($id) {
		$this->id = (string) $id;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if ($this->type !== null || $this->id !== null) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [
			'type' => $this->type,
			'id'   => $this->id,
		];
		
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getResource($identifierOnly=false) {
		return $this;
	}
}
