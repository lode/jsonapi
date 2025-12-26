<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\enums\ObjectContainerEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

/**
 * @internal
 */
class Validator {
	/** @var array<string, ObjectContainerEnum> */
	protected array $usedFields = [];
	/** @var array<string, true> */
	protected array $usedResourceIdentifiers = [];
	/** @var PHPStanTypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * blocks 'type' as a keyword inside attributes or relationships
		 * the specification doesn't allow this as 'type' is already set at the root of a resource
		 * set to true if migrating to jsonapi and currently using 'type' as attribute or relationship
		 */
		'enforceTypeFieldNamespace' => true,
	];
	
	/**
	 * block if already existing in another object, otherwise just overwrite
	 * 
	 * @see https://jsonapi.org/format/1.1/#document-resource-object-fields
	 * 
	 * @param  string[]                         $fieldNames
	 * @param  PHPStanTypeAlias_InternalOptions $options    {@see Validator::$defaults}
	 * 
	 * @throws DuplicateException
	 */
	public function claimUsedFields(array $fieldNames, ObjectContainerEnum $objectContainer, array $options=[]): void {
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
			if ($this->usedFields[$fieldName] === ObjectContainerEnum::Type && $options['enforceTypeFieldNamespace'] === false) {
				continue;
			}
			
			throw new DuplicateException('field name "'.$fieldName.'" already in use at "data.'.$this->usedFields[$fieldName]->value.'"');
		}
	}
	
	public function clearUsedFields(ObjectContainerEnum $objectContainerToClear): void {
		foreach ($this->usedFields as $fieldName => $containerFound) {
			if ($containerFound !== $objectContainerToClear) {
				continue;
			}
			
			unset($this->usedFields[$fieldName]);
		}
	}
	
	/**
	 * @throws InputException if no type or id has been set on the resource
	 * @throws DuplicateException if the combination of type and id has been set before
	 */
	public function claimUsedResourceIdentifier(ResourceInterface $resource): void {
		if ($resource->getResource()->hasIdentification() === false) {
			throw new InputException('can not validate resource without identifier, set type and id/lid first');
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
	 * @throws InputException
	 */
	public static function checkMemberName(string $memberName): void {
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
	
	public static function checkHttpStatusCode(string|int $httpStatusCode): bool {
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
