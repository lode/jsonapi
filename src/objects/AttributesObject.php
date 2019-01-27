<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Converter;
use alsvanzelf\jsonapi\Validator;

class AttributesObject {
	/** @var array */
	public $attributes = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  array $attributes
	 * @return AttributesObject
	 */
	public static function fromArray(array $attributes) {
		$attributesObject = new self();
		
		foreach ($attributes as $key => $value) {
			$attributesObject->add($key, $value);
		}
		
		return $attributesObject;
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function add($key, $value) {
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->attributes[$key] = $value;
	}
	
	/**
	 * output
	 */
	
	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->attributes === []);
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return $this->attributes;
	}
}
