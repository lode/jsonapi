<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Converter;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;

class MetaObject implements ObjectInterface {
	/** @var array */
	public $meta = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  array $meta
	 * @return MetaObject
	 */
	public static function fromArray(array $meta) {
		$metaObject = new self();
		
		foreach ($meta as $key => $value) {
			$metaObject->add($key, $value);
		}
		
		return $metaObject;
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
		
		$this->meta[$key] = $value;
	}
	
	/**
	 * output
	 */
	
	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->meta === []);
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return $this->meta;
	}
}
