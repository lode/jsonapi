<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\HasAttributesInterface;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject implements HasAttributesInterface, RecursiveResourceContainerInterface {
	use LinksManager;
	
	/** @var AttributesObject */
	protected $attributes;
	/** @var RelationshipsObject */
	protected $relationships;
	/** @var array */
	protected static $defaults = [
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
	 * @param  array      $attributes
	 * @param  string     $type       optional
	 * @param  string|int $id         optional
	 * @param  array      $options    optional {@see ResourceObject::$defaults}
	 * @return ResourceObject
	 */
	public static function fromArray(array $attributes, $type=null, $id=null, array $options=[]) {
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
	 * @param  object     $attributes
	 * @param  string     $type       optional
	 * @param  string|int $id         optional
	 * @param  array      $options    optional {@see ResourceObject::$defaults}
	 * @return ResourceObject
	 */
	public static function fromObject($attributes, $type=null, $id=null, array $options=[]) {
		$array = Converter::objectToArray($attributes);
		
		return self::fromArray($array, $type, $id, $options);
	}
	
	/**
	 * add key-value pairs to attributes
	 * 
	 * @param string $key
	 * @param mixed  $value
	 * @param array  $options optional {@see ResourceObject::$defaults}
	 */
	public function add($key, $value, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		if ($this->attributes === null) {
			$this->attributes = new AttributesObject();
		}
		
		$this->validator->claimUsedFields([$key], Validator::OBJECT_CONTAINER_ATTRIBUTES, $options);
		
		$this->attributes->add($key, $value);
	}
	
	/**
	 * @param  string $key
	 * @param  mixed  $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param  array  $links    optional
	 * @param  array  $meta     optional
	 * @param  array  $options  optional {@see ResourceObject::$defaults}
	 * @return RelationshipObject
	 */
	public function addRelationship($key, $relation, array $links=[], array $meta=[], array $options=[]) {
		$relationshipObject = RelationshipObject::fromAnything($relation, $links, $meta);
		
		$this->addRelationshipObject($key, $relationshipObject, $options);
		
		return $relationshipObject;
	}
	
	/**
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setSelfLink($href, array $meta=[]) {
		$this->addLink('self', $href, $meta);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param AttributesObject $attributesObject
	 * @param array            $options          optional {@see ResourceObject::$defaults}
	 */
	public function setAttributesObject(AttributesObject $attributesObject, array $options=[]) {
		$newKeys = $attributesObject->getKeys();
		$this->validator->clearUsedFields(Validator::OBJECT_CONTAINER_ATTRIBUTES);
		$this->validator->claimUsedFields($newKeys, Validator::OBJECT_CONTAINER_ATTRIBUTES, $options);
		
		$this->attributes = $attributesObject;
	}
	
	/**
	 * @param string             $key
	 * @param RelationshipObject $relationshipObject
	 * @param array              $options            optional {@see ResourceObject::$defaults}
	 * 
	 * @throws DuplicateException if the resource is contained as a resource in the relationship
	 */
	public function addRelationshipObject($key, RelationshipObject $relationshipObject, array $options=[]) {
		if ($relationshipObject->hasResource($this)) {
			throw new DuplicateException('can not add relation to self');
		}
		
		if ($this->relationships === null) {
			$this->setRelationshipsObject(new RelationshipsObject());
		}
		
		$this->validator->claimUsedFields([$key], Validator::OBJECT_CONTAINER_RELATIONSHIPS, $options);
		
		$this->relationships->addRelationshipObject($key, $relationshipObject);
	}
	
	/**
	 * @param RelationshipsObject $relationshipsObject
	 */
	public function setRelationshipsObject(RelationshipsObject $relationshipsObject) {
		$newKeys = $relationshipsObject->getKeys();
		$this->validator->clearUsedFields(Validator::OBJECT_CONTAINER_RELATIONSHIPS);
		$this->validator->claimUsedFields($newKeys, Validator::OBJECT_CONTAINER_RELATIONSHIPS);
		
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
	 * 
	 * @return boolean
	 */
	public function hasIdentifierPropertiesOnly() {
		if ($this->attributes !== null && $this->attributes->isEmpty() === false) {
			return false;
		}
		if ($this->relationships !== null && $this->relationships->isEmpty() === false) {
			return false;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * HasAttributesInterface
	 */
	
	public function addAttribute(string $key, $value, array $options=[]) {
		return $this->add($key, $value);
	}
	
	/**
	 * ResourceInterface
	 */
	
	public function getResource($identifierOnly=false) {
		if ($identifierOnly) {
			return ResourceIdentifierObject::fromResourceObject($this);
		}
		
		return $this;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty() {
		if (parent::isEmpty() === false) {
			return false;
		}
		if ($this->attributes !== null && $this->attributes->isEmpty() === false) {
			return false;
		}
		if ($this->relationships !== null && $this->relationships->isEmpty() === false) {
			return false;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	public function toArray() {
		$array = parent::toArray();
		
		if ($this->attributes !== null && $this->attributes->isEmpty() === false) {
			$array['attributes'] = $this->attributes->toArray();
		}
		if ($this->relationships !== null && $this->relationships->isEmpty() === false) {
			$array['relationships'] = $this->relationships->toArray();
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	public function getNestedContainedResourceObjects() {
		if ($this->relationships === null) {
			return [];
		}
		
		return $this->relationships->getNestedContainedResourceObjects();
	}
}
