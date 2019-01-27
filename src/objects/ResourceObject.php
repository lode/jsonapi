<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject {
	/** @var AttributesObject */
	public $attributes;
	
	/**
	 * human api
	 */
	
	/**
	 * @param  array      $attributes
	 * @param  string     $type optional
	 * @param  string|int $id   optional
	 * @return ResourceObject
	 */
	public static function fromArray(array $attributes, $type=null, $id=null) {
		$resourceObject = new self($type, $id);
		$resourceObject->setAttributesObject(AttributesObject::fromArray($attributes));
		
		return $resourceObject;
	}
	
	/**
	 * add key-value pairs to attributes
	 * 
	 * @param string $key
	 * @param mixed  $value
	 */
	public function add($key, $value) {
		if ($this->attributes === null) {
			$this->attributes = new AttributesObject();
		}
		
		$this->validator->checkUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES);
		
		$this->attributes->add($key, $value);
		
		$this->validator->markUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param AttributesObject $attributesObject
	 */
	public function setAttributesObject(AttributesObject $attributesObject) {
		$this->attributes = $attributesObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if (parent::isEmpty() === false) {
			return false;
		}
		if ($this->attributes !== null && $this->attributes->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		if ($this->attributes !== null && $this->attributes->isEmpty() === false) {
			$array['attributes'] = $this->attributes->toArray();
		}
		
		return $array;
	}
}
