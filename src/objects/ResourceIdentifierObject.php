<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ResourceIdentifierObject implements ResourceInterface {
	public $type;
	public $id;
	protected $fields = [];
	
	public function __construct($type, $id) {
		$this->type = $type;
		$this->id   = (string) $id;
		
		$this->markUsedField($fieldName='type', $objectContainer='type');
		$this->markUsedField($fieldName='id', $objectContainer='id');
	}
	
	/**
	 * spec api
	 */
	
	public function setType(string $type) {
		$this->type = $type;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource() {
		return $this;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * block if already existing in another object, otherwise just overwrite
	 * 
	 * @see https://jsonapi.org/format/1.1/#document-resource-object-fields
	 * 
	 * @param  string $fieldName
	 * @param  string $objectContainer one of 'type', 'id', 'attributes', 'relationships'
	 * 
	 * @throws DuplicateException
	 */
	protected function checkUsedField($fieldName, $objectContainer) {
		if (isset($this->fields[$fieldName]) === false) {
			return;
		}
		if ($this->fields[$fieldName] === $objectContainer) {
			return;
		}
		
		throw new DuplicateException('field name "'.$fieldName.'" already in use at "data.'.$this->fields[$fieldName].'"');
	}
	
	protected function markUsedField($fieldName, $objectContainer) {
		$this->fields[$fieldName] = $objectContainer;
	}
}
