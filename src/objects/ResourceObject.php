<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
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
	 * add key-value pairs to attributes
	 * 
	 * @param string $key
	 * @param mixed  $value
	 */
	public function add($key, $value) {
		if ($this->attributes === null) {
			$this->attributes = new AttributesObject();
		}
		
		$this->validator->checkUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES);
		
		$this->attributes->add($key, $value);
		
		$this->validator->markUsedField($key, Validator::OBJECT_CONTAINER_ATTRIBUTES);
	}
	
	/**
	 * @param string $key
	 * @param mixed  $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param array  $links    optional
	 * @param array  $meta     optional
	 */
	public function addRelationship($key, $relation, array $links=[], array $meta=[]) {
		if ($this->relationships === null) {
			$this->setRelationshipsObject(new RelationshipsObject());
		}
		
		$this->relationships->add($key, $relation, $links, $meta);
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
