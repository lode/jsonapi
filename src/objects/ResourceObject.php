<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\Converter;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject {
	/** @var AttributesObject */
	public $attributes;
	/** @var RelationshipsObject */
	public $relationships;
	/** @var LinksObject */
	public $links;
	/** @var array */
	private static $defaults = [
		/**
		 * set to false to allow using 'type' as a member in attributes or relationships
		 * @note this is not allowed by the specification
		 */
		'enforceTypeFieldNamespace' => true,
	];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  array      $attributes
	 * @param  string     $type optional
	 * @param  string|int $id   optional
	 * @return ResourceObject
	 */
	public static function fromArray(array $attributes, $type=null, $id=null) {
		$resourceObject = new self($type, $id);
		$resourceObject->setAttributesObject(AttributesObject::fromArray($attributes));
		
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
		
		$this->validator->checkUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES, $options);
		
		$this->attributes->add($key, $value);
		
		$this->validator->markUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES);
	}
	
	/**
	 * @param  string $key
	 * @param  mixed  $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param  array  $links    optional
	 * @param  array  $meta     optional
	 * @return RelationshipObject
	 */
	public function addRelationship($key, $relation, array $links=[], array $meta=[]) {
		if ($this->relationships === null) {
			$this->setRelationshipsObject(new RelationshipsObject());
		}
		
		return $this->relationships->add($key, $relation, $links, $meta);
	}
	
	/**
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function addLink($key, $href, array $meta=[]) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->add($key, $href, $meta);
	}
	
	/**
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setSelfLink($href, array $meta=[]) {
		$this->addLink('self', $href, $meta);
	}
	
	/**
	 * whether the ResourceObject is empty except for the ResourceIdentifierObject
	 * 
	 * this can be used to determine if a Relationship's resource could be added as included resource
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
	 * get ResourceObjects from inside all RelationshipsObjects which are not only a ResourceIdentifierObject
	 * 
	 * this can be used to add included ResourceObjects on a DataDocument
	 * 
	 * @return ResourceObject[]
	 */
	public function getRelatedResourceObjects() {
		if ($this->relationships === null) {
			return [];
		}
		
		return $this->relationships->getRelatedResourceObjects();
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param AttributesObject $attributesObject
	 */
	public function setAttributesObject(AttributesObject $attributesObject) {
		$this->attributes = $attributesObject;
	}
	
	/**
	 * @param RelationshipObject $relationshipObject
	 * @param string             $key                optional, required if $relationshipObject has no key defined
	 * 
	 * @throws DuplicateException if the resource is contained as a resource in the relationship
	 */
	public function addRelationshipObject(RelationshipObject $relationshipObject, $key=null) {
		if ($relationshipObject->hasResource($this)) {
			throw new DuplicateException('can not add relation to self');
		}
		
		if ($this->relationships === null) {
			$this->setRelationshipsObject(new RelationshipsObject());
		}
		
		$this->relationships->addRelationshipObject($relationshipObject, $key);
	}
	
	/**
	 * @param RelationshipsObject $relationshipsObject
	 */
	public function setRelationshipsObject(RelationshipsObject $relationshipsObject) {
		$this->relationships = $relationshipsObject;
	}
	
	/**
	 * @param LinkObject $linkObject
	 * @param string     $key        optional, required if $linkObject has no key defined
	 */
	public function addLinkObject(LinkObject $linkObject, $key=null) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->addLinkObject($linkObject);
	}
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
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
	
	/**
	 * @inheritDoc
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
	
	/**
	 * @inheritDoc
	 */
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
}
