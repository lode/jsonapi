<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;

class AttributesObject implements ObjectInterface {
	use AtMemberManager;
	
	/** @var array */
	protected $attributes = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @note if an `id` is set inside $attributes, it is removed from there
	 *       it is common to find it inside, and not doing so will cause an exception
	 * 
	 * @param  array $attributes
	 * @return AttributesObject
	 */
	public static function fromArray(array $attributes) {
		unset($attributes['id']);
		
		$attributesObject = new self();
		
		foreach ($attributes as $key => $value) {
			$attributesObject->add($key, $value);
		}
		
		return $attributesObject;
	}
	
	/**
	 * @param  object $attributes
	 * @return AttributesObject
	 */
	public static function fromObject($attributes) {
		$array = Converter::objectToArray($attributes);
		
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
		
		$this->attributes[$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @return string[]
	 */
	public function getKeys() {
		return array_keys($this->attributes);
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return ($this->attributes === [] && $this->hasAtMembers() === false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		return array_merge($this->getAtMembers(), $this->attributes);
	}
}
