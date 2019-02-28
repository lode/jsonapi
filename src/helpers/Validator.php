<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

/**
 * @internal
 */
class Validator {
	const OBJECT_CONTAINER_TYPE          = 'type';
	const OBJECT_CONTAINER_ID            = 'id';
	const OBJECT_CONTAINER_ATTRIBUTES    = 'attributes';
	const OBJECT_CONTAINER_RELATIONSHIPS = 'relationships';
	
	/** @var array */
	protected $usedFields = [];
	/** @var array */
	protected $usedResourceIdentifiers = [];
	/** @var array */
	protected static $defaults = [
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
	 * @param  string[] $fieldName
	 * @param  string   $objectContainer one of the Validator::OBJECT_CONTAINER_* constants
	 * @param  array    $options         optional {@see Validator::$defaults}
	 * 
	 * @throws DuplicateException
	 */
	public function claimUsedFields(array $fieldNames, $objectContainer, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		foreach ($fieldNames as $fieldName) {
			if (isset($this->usedFields[$fieldName]) === false) {
				$this->usedFields[$fieldName] = $objectContainer;
				continue;
			}
			if ($this->usedFields[$fieldName] === $objectContainer) {
				continue;
			}
			
			/**
			 * @note this is not allowed by the specification
			 */
			if ($this->usedFields[$fieldName] === Validator::OBJECT_CONTAINER_TYPE && $options['enforceTypeFieldNamespace'] === false) {
				continue;
			}
			
			throw new DuplicateException('field name "'.$fieldName.'" already in use at "data.'.$this->usedFields[$fieldName].'"');
		}
	}
	
	/**
	 * @param string $objectContainer one of the Validator::OBJECT_CONTAINER_* constants
	 */
	public function clearUsedFields($objectContainerToClear) {
		foreach ($this->usedFields as $fieldName => $containerFound) {
			if ($containerFound !== $objectContainerToClear) {
				continue;
			}
			
			unset($this->usedFields[$fieldName]);
		}
	}
	
	/**
	 * @param  ResourceInterface $resource
	 * 
	 * @throws InputException if no type or id has been set on the resource
	 * @throws DuplicateException if the combination of type and id has been set before
	 */
	public function claimUsedResourceIdentifier(ResourceInterface $resource) {
		if ($resource->getResource()->hasIdentification() === false) {
			throw new InputException('can not validate resource without identifier, set type and id first');
		}
		
		$resourceKey = $resource->getResource()->getIdentificationKey();
		if (isset($this->usedResourceIdentifiers[$resourceKey]) === false) {
			$this->usedResourceIdentifiers[$resourceKey] = true;
			return;
		}
		
		throw new DuplicateException('can not have multiple resources with the same identification');
	}
	
	/**
	 * @see https://jsonapi.org/format/1.1/#document-member-names
	 * 
	 * @todo allow non-url safe chars
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
