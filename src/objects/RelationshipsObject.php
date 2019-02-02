<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class RelationshipsObject implements ObjectInterface {
	/** @var RelationshipObject[] */
	public $relationships = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  string $key
	 * @param  mixed  $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param  array  $links    optional
	 * @param  array  $meta     optional
	 * @return RelationshipObject
	 */
	public function add($key, $relation, array $links=[], array $meta=[]) {
		$relationshipObject = RelationshipObject::fromAnything($relation, $links, $meta);
		
		$this->addRelationshipObject($relationshipObject, $key);
		
		return $relationshipObject;
	}
	
	/**
	 * get ResourceObjects from inside all RelationshipObjects which are not only a ResourceIdentifierObject
	 * 
	 * this can be used to add included ResourceObjects on a DataDocument
	 * 
	 * @return ResourceObject[]
	 */
	public function getRelatedResourceObjects() {
		$resourceObjects = [];
		
		foreach ($this->relationships as $relationship) {
			$resourceObjects = array_merge($resourceObjects, $relationship->getRelatedResourceObjects());
		}
		
		return $resourceObjects;
	}
	
	/**
	 * @return string[]
	 */
	public function getKeys() {
		return array_keys($this->relationships);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param RelationshipObject $relationshipObject
	 * @param string             $key                optional, required if $relationshipObject has no key defined
	 * 
	 * @throws InputException     if $key is not given and $relationshipObject has no key defined
	 * @throws DuplicateException if another relationship is already using that $key
	 */
	public function addRelationshipObject(RelationshipObject $relationshipObject, $key=null) {
		if ($key === null && $relationshipObject->key === null) {
			throw new InputException('key not given nor defined inside the RelationshipObject');
		}
		elseif ($key === null) {
			$key = $relationshipObject->key;
		}
		else {
			Validator::checkMemberName($key);
		}
		
		if (isset($this->relationships[$key])) {
			throw new DuplicateException('relationship with key "'.$key.'" already set');
		}
		
		$this->relationships[$key] = $relationshipObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return ($this->relationships === []);
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		foreach ($this->relationships as $key => $relationshipObject) {
			if ($relationshipObject->isEmpty()) {
				continue;
			}
			
			$array[$key] = $relationshipObject->toArray();
		}
		
		return $array;
	}
}
