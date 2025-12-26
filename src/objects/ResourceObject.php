<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\enums\ObjectContainerEnum;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\interfaces\HasAttributesInterface;
use alsvanzelf\jsonapi\interfaces\HasLinksInterface;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject implements HasAttributesInterface, HasLinksInterface, RecursiveResourceContainerInterface {
	use LinksManager;
	
	protected AttributesObject $attributes;
	protected RelationshipsObject $relationships;
	/** @var TypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * blocks 'type' as a keyword inside attributes or relationships
		 * the specification doesn't allow this as 'type' is already set at the root of a resource
		 * set to true if migrating to jsonapi and currently using 'type' as attribute or relationship
		 */
		'enforceTypeFieldNamespace' => true,
	];
	
	/**
	 * human api
	 */
	
	/**
	 * @note if an `id` is set inside $attributes, it is removed from there
	 *       and if $id is null, it is filled with that value
	 *       it is common to find it inside, and not doing so will cause an exception
	 * 
	 * @param array<string, mixed>      $attributes
	 * @param TypeAlias_InternalOptions $options    {@see ResourceObject::$defaults}
	 */
	public static function fromArray(array $attributes, ?string $type=null, string|int|null $id=null, array $options=[]): self {
		if (isset($attributes['id'])) {
			if ($id === null) {
				$id = $attributes['id'];
			}
			
			unset($attributes['id']);
		}
		
		$resourceObject = new self($type, $id);
		$resourceObject->setAttributesObject(AttributesObject::fromArray($attributes), $options);
		
		return $resourceObject;
	}
	
	/**
	 * @param TypeAlias_InternalOptions $options {@see ResourceObject::$defaults}
	 */
	public static function fromObject(object $attributes, ?string $type=null, string|int|null $id=null, array $options=[]): self {
		$array = Converter::objectToArray($attributes);
		
		return self::fromArray($array, $type, $id, $options);
	}
	
	/**
	 * add key-value pairs to attributes
	 * 
	 * @param TypeAlias_InternalOptions $options {@see ResourceObject::$defaults}
	 */
	public function add(string $key, mixed $value, array $options=[]): void {
		$options = array_merge(self::$defaults, $options);
		
		if (isset($this->attributes) === false) {
			$this->attributes = new AttributesObject();
		}
		
		$this->validator->claimUsedFields([$key], ObjectContainerEnum::Attributes, $options);
		
		$this->attributes->add($key, $value);
	}
	
	/**
	 * @param CollectionDocument|ResourceInterface|ResourceInterface[]|null $relation 
	 * @param array<string, ?string>                                        $links
	 * @param array<array-key, mixed>                                       $meta
	 * @param TypeAlias_InternalOptions $options {@see ResourceObject::$defaults}
	 */
	public function addRelationship(
		string $key,
		array|CollectionDocument|ResourceInterface|null $relation,
		array $links=[],
		array $meta=[],
		array $options=[],
	): RelationshipObject {
		$relationshipObject = RelationshipObject::fromAnything($relation, $links, $meta);
		
		$this->addRelationshipObject($key, $relationshipObject, $options);
		
		return $relationshipObject;
	}
	
	/**
	 * @param array<array-key, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setSelfLink(string $href, array $meta=[]): void {
		$this->addLink('self', $href, $meta);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param TypeAlias_InternalOptions $options {@see ResourceObject::$defaults}
	 */
	public function setAttributesObject(AttributesObject $attributesObject, array $options=[]): void {
		$newKeys = $attributesObject->getKeys();
		$this->validator->clearUsedFields(ObjectContainerEnum::Attributes);
		$this->validator->claimUsedFields($newKeys, ObjectContainerEnum::Attributes, $options);
		
		$this->attributes = $attributesObject;
	}
	
	/**
	 * @param TypeAlias_InternalOptions $options {@see ResourceObject::$defaults}
	 * 
	 * @throws DuplicateException if the resource is contained as a resource in the relationship
	 */
	public function addRelationshipObject(string $key, RelationshipObject $relationshipObject, array $options=[]): void {
		if ($relationshipObject->hasResource($this)) {
			throw new DuplicateException('can not add relation to self');
		}
		
		if (isset($this->relationships) === false) {
			$this->setRelationshipsObject(new RelationshipsObject());
		}
		
		$this->validator->claimUsedFields([$key], ObjectContainerEnum::Relationships, $options);
		
		$this->relationships->addRelationshipObject($key, $relationshipObject);
	}
	
	public function setRelationshipsObject(RelationshipsObject $relationshipsObject): void {
		$newKeys = $relationshipsObject->getKeys();
		$this->validator->clearUsedFields(ObjectContainerEnum::Relationships);
		$this->validator->claimUsedFields($newKeys, ObjectContainerEnum::Relationships);
		
		$this->relationships = $relationshipsObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * whether the ResourceObject is empty except for the ResourceIdentifierObject
	 * 
	 * this can be used to determine if a Relationship's resource could be added as included resource
	 * 
	 * @internal
	 */
	public function hasIdentifierPropertiesOnly(): bool {
		if (isset($this->attributes) && $this->attributes->isEmpty() === false) {
			return false;
		}
		if (isset($this->relationships) && $this->relationships->isEmpty() === false) {
			return false;
		}
		if ($this->hasLinks()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * HasAttributesInterface
	 */
	
	public function addAttribute(string $key, mixed $value, array $options=[]): void {
		$this->add($key, $value);
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource(bool $identifierOnly=false): ResourceIdentifierObject|ResourceObject {
		if ($identifierOnly) {
			return ResourceIdentifierObject::fromResourceObject($this);
		}
		
		return $this;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if (parent::isEmpty() === false) {
			return false;
		}
		if (isset($this->attributes) && $this->attributes->isEmpty() === false) {
			return false;
		}
		if (isset($this->relationships) && $this->relationships->isEmpty() === false) {
			return false;
		}
		if ($this->hasLinks()) {
			return false;
		}
		
		return true;
	}
	
	public function toArray(): array {
		$array = parent::toArray();
		
		if (isset($this->attributes) && $this->attributes->isEmpty() === false) {
			$array['attributes'] = $this->attributes->toArray();
		}
		if (isset($this->relationships) && $this->relationships->isEmpty() === false) {
			$array['relationships'] = $this->relationships->toArray();
		}
		if ($this->hasLinks()) {
			$array['links'] = $this->links->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	public function getNestedContainedResourceObjects(): array {
		if (isset($this->relationships) === false) {
			return [];
		}
		
		return $this->relationships->getNestedContainedResourceObjects();
	}
}
