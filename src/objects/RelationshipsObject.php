<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class RelationshipsObject extends AbstractObject implements RecursiveResourceContainerInterface {
	/** @var RelationshipObject[] */
	protected $relationships = [];
	
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
		
		$this->addRelationshipObject($key, $relationshipObject);
		
		return $relationshipObject;
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string             $key
	 * @param RelationshipObject $relationshipObject
	 * 
	 * @throws DuplicateException if another relationship is already using that $key
	 */
	public function addRelationshipObject($key, RelationshipObject $relationshipObject) {
		Validator::checkMemberName($key);
		
		if (isset($this->relationships[$key])) {
			throw new DuplicateException('relationship with key "'.$key.'" already set');
		}
		
		$this->relationships[$key] = $relationshipObject;
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
		return array_keys($this->relationships);
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty() {
		if ($this->relationships !== []) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		if ($this->hasExtensionMembers()) {
			return false;
		}
		
		return true;
	}
	
	public function toArray() {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		
		foreach ($this->relationships as $key => $relationshipObject) {
			$array[$key] = $relationshipObject->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	public function getNestedContainedResourceObjects() {
		$resourceObjects = [];
		
		foreach ($this->relationships as $relationship) {
			$resourceObjects = array_merge($resourceObjects, $relationship->getNestedContainedResourceObjects());
		}
		
		return $resourceObjects;
	}
}
