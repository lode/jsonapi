<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;

class Validator {
	const OBJECT_CONTAINER_TYPE          = 'type';
	const OBJECT_CONTAINER_ID            = 'id';
	const OBJECT_CONTAINER_ATTRIBUTES    = 'attributes';
	const OBJECT_CONTAINER_RELATIONSHIPS = 'relationships';
	
	private $usedFields = [];
	
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
	public function checkUsedField($fieldName, $objectContainer) {
		if (isset($this->usedFields[$fieldName]) === false) {
			return;
		}
		if ($this->usedFields[$fieldName] === $objectContainer) {
			return;
		}
		
		throw new DuplicateException('field name "'.$fieldName.'" already in use at "data.'.$this->usedFields[$fieldName].'"');
	}
	
	public function markUsedField($fieldName, $objectContainer) {
		$this->usedFields[$fieldName] = $objectContainer;
	}
	
	/**
	 * @see https://jsonapi.org/format/1.1/#document-member-names
	 * 
	 * @todo allow non-url safe chars
	 * @todo allow @-members for JSON-LD {@see https://jsonapi.org/format/1.1/#document-member-names-at-members}
	 * 
	 * @param  string $memberName
	 * 
	 * @throws InputException
	 */
	public static function checkMemberName($memberName) {
		$globallyAllowedCharacters  = 'a-zA-Z0-9';
		$generallyAllowedCharacters = $globallyAllowedCharacters.'_-';
		
		$regex = '{^
			(
				['.$globallyAllowedCharacters.']
				
				|
				
				['.$globallyAllowedCharacters.']
				['.$generallyAllowedCharacters.']*
				['.$globallyAllowedCharacters.']
			)
		$}x';
		
		if (preg_match($regex, $memberName) === 1) {
			return;
		}
		
		throw new InputException('invalid member name "'.$memberName.'"');
	}
}
