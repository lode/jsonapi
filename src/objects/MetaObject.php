<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;

class MetaObject implements ObjectInterface {
	/** @var array */
	protected $meta = [];
	
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
	 * @param  object $meta
	 * @return MetaObject
	 */
	public static function fromObject($meta) {
		$array = Converter::objectToArray($meta);
		
		return self::fromArray($array);
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
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return ($this->meta === []);
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		return $this->meta;
	}
}
