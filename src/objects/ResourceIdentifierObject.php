<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ResourceIdentifierObject implements ResourceInterface {
	/** @var string */
	public $type;
	/** @var string */
	public $id;
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
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getResource() {
		return $this;
	}
	
	/**
	 * output
	 */
	
	/**
	 * @return boolean
	 */
	public function isEmpty() {
		if ($this->type !== null || $this->id !== null) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		$array = [
			'type' => $this->type,
			'id'   => $this->id,
		];
		
		return $array;
	}
}
