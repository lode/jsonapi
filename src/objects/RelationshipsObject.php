<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;

class RelationshipsObject extends AbstractObject implements RecursiveResourceContainerInterface {
	/** @var array<string, RelationshipObject> */
	protected array $relationships = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  CollectionDocument|ResourceInterface|ResourceInterface[]|null $relation 
	 * @param  array<string, ?string>                                        $links
	 * @param  array<string, mixed>                                          $meta
	 */
	public function add(
		string $key,
		array|CollectionDocument|ResourceInterface|null $relation,
		array $links=[],
		array $meta=[],
	): RelationshipObject {
		$relationshipObject = RelationshipObject::fromAnything($relation, $links, $meta);
		
		$this->addRelationshipObject($key, $relationshipObject);
		
		return $relationshipObject;
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @throws DuplicateException if another relationship is already using that $key
	 */
	public function addRelationshipObject(string $key, RelationshipObject $relationshipObject): void {
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
	public function getKeys(): array {
		return array_keys($this->relationships);
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
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
	
	public function toArray(): array {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = [...$array, ...$this->getAtMembers()];
		}
		if ($this->hasExtensionMembers()) {
			$array = [...$array, ...$this->getExtensionMembers()];
		}
		
		foreach ($this->relationships as $key => $relationshipObject) {
			$array[$key] = $relationshipObject->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	public function getNestedContainedResourceObjects(): array {
		$resourceObjects = [];
		
		foreach ($this->relationships as $relationship) {
			$resourceObjects = [...$resourceObjects, ...$relationship->getNestedContainedResourceObjects()];
		}
		
		return $resourceObjects;
	}
}
