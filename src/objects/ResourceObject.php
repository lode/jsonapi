<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject {
	public $attributes = [];
	
	/**
	 * human api
	 */
	
	/**
	 * add key-value pairs to attributes
	 * 
	 * @param string $key
	 * @param mixed  $value objects will be converted using `get_object_vars()`
	 */
	public function addData($key, $value) {
		$this->checkMemberName($key);
		$this->checkUsedField($key, $objectContainer='attributes');
		
		if (is_object($value)) {
			$value = get_object_vars($value);
		}
		
		$this->attributes[$key] = $value;
		$this->markUsedField($key, $objectContainer='attributes');
	}
	
	public function setData(array $array) {
		foreach ($array as $key => $value) {
			$this->addData($key, $value);
		}
	}
	
	/**
	 * internal api
	 */
	
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
	private function checkMemberName($memberName) {
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
