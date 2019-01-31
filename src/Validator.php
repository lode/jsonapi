<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class Validator {
	const OBJECT_CONTAINER_TYPE          = 'type';
	const OBJECT_CONTAINER_ID            = 'id';
	const OBJECT_CONTAINER_ATTRIBUTES    = 'attributes';
	const OBJECT_CONTAINER_RELATIONSHIPS = 'relationships';
	
	/** @var array */
	private $usedFields = [];
	/** @var array */
	private $usedResourceIdentifiers = [];
	/** @var array */
	private static $defaults = [
		/**
		 * @note this is not allowed by the specification
		 */
		'enforceTypeFieldNamespace' => true,
	];
	
	/**
	 * block if already existing in another object, otherwise just overwrite
	 * 
	 * @see https://jsonapi.org/format/1.1/#document-resource-object-fields
	 * 
	 * @param  string $fieldName
	 * @param  string $objectContainer one of the Validator::OBJECT_CONTAINER_* constants
	 * @param  array  $options         optional {@see Validator::$defaults}
	 * 
	 * @throws DuplicateException
	 */
	public function checkUsedField($fieldName, $objectContainer, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		if (isset($this->usedFields[$fieldName]) === false) {
			return;
		}
		if ($this->usedFields[$fieldName] === $objectContainer) {
			return;
		}
		
		/**
		 * @note this is not allowed by the specification
		 */
		if ($this->usedFields[$fieldName] === Validator::OBJECT_CONTAINER_TYPE && $options['enforceTypeFieldNamespace'] === false) {
			return;
		}
		
		throw new DuplicateException('field name "'.$fieldName.'" already in use at "data.'.$this->usedFields[$fieldName].'"');
	}
	
	/**
	 * @param  string $fieldName
	 * @param  string $objectContainer one of the Validator::OBJECT_CONTAINER_* constants
	 */
	public function markUsedField($fieldName, $objectContainer) {
		$this->usedFields[$fieldName] = $objectContainer;
	}
	
	/**
	 * @param  ResourceInterface $resource
	 * 
	 * @throws InputException if no type or id has been set on the resource
	 * @throws DuplicateException if the combination of type and id has been set before
	 */
	public function checkUsedResourceIdentifier(ResourceInterface $resource) {
		if ($resource->getResource()->hasIdentification() === false) {
			throw new InputException('can not validate resource without identifier, set type and id first');
		}
		
		$resourceKey = $resource->getResource()->getIdentificationKey();
		if (isset($this->usedResourceIdentifiers[$resourceKey]) === false) {
			return;
		}
		
		throw new DuplicateException('can not have multiple resources with the same identification');
	}
	
	/**
	 * @param  ResourceInterface $resource
	 */
	public function markUsedResourceIdentifier(ResourceInterface $resource) {
		$resourceKey = $resource->getResource()->getIdentificationKey();
		$this->usedResourceIdentifiers[$resourceKey] = true;
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
	
	/**
	 * @param  string|int $httpStatusCode
	 * @return boolean
	 */
	public static function checkHttpStatusCode($httpStatusCode) {
		$httpStatusCode = (int) $httpStatusCode;
		
		if ($httpStatusCode < 100) {
			return false;
		}
		if ($httpStatusCode >= 600) {
			return false;
		}
		
		return true;
	}
}
