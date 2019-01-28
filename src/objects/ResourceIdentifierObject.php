<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
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
	}
	
	/**
	 * human api
	 */
	
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
	 * spec api
	 */
	
	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
		
		$this->validator->markUsedField($fieldName='type', Validator::OBJECT_CONTAINER_TYPE);
	}
	
	/**
	 * @param string|int $id will be casted to a string
	 */
	public function setId($id) {
		$this->id = (string) $id;
		
		$this->validator->markUsedField($fieldName='id', Validator::OBJECT_CONTAINER_ID);
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getResource() {
		return $this;
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
}