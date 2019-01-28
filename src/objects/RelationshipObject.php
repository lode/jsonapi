<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;

class RelationshipObject implements ObjectInterface {
	const TO_ONE  = 'one';
	const TO_MANY = 'many';
	
	/** @var LinksObject */
	public $links;
	/** @var MetaObject */
	public $meta;
	/** @var string */
	public $key;
	/** @var string one of the RelationshipObject::TO_* constants */
	protected $type;
	/** @var ResourceInterface */
	protected $resource;
	/** @var ResourceInterface[] */
	protected $resources = [];
	
	/**
	 * @param string $type one of the RelationshipObject::TO_* constants
	 * 
	 * @throws InputException if $type is unknown
	 */
	public function __construct($type) {
		if (in_array($type, [RelationshipObject::TO_ONE, RelationshipObject::TO_MANY], $strict=true) === false) {
			throw new InputException('unknown relationship type "'.$type.'"');
		}
		
		$this->type = $type;
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param  ResourceInterface $resource
	 * @param  array             $links    optional
	 * @param  array             $meta     optional
	 * @param  string            $type     optional, one of the RelationshipObject::TO_* constants, defaults to RelationshipObject::TO_ONE
	 * @return RelationshipObject
	 */
	public static function fromResource(ResourceInterface $resource, array $links=[], array $meta=[], $type=RelationshipObject::TO_ONE) {
		$relationshipObject = new self($type);
		
		if ($type === RelationshipObject::TO_ONE) {
			$relationshipObject->setResource($resource);
		}
		elseif ($type === RelationshipObject::TO_MANY) {
			$relationshipObject->addResource($resource);
		}
		
		if ($links !== []) {
			$relationshipObject->setLinksObject(LinksObject::fromArray($links));
		}
		if ($meta !== []) {
			$relationshipObject->setMetaObject(MetaObject::fromArray($meta));
		}
		
		return $relationshipObject;
	}
	
	/**
	 * @param  CollectionDocument $collectionDocument
	 * @param  array              $links              optional
	 * @param  array              $meta               optional
	 * @return RelationshipObject
	 */
	public static function fromCollectionDocument(CollectionDocument $collectionDocument, array $links=[], array $meta=[]) {
		$relationshipObject = new self(RelationshipObject::TO_MANY);
		
		foreach ($collectionDocument->resources as $resource) {
			$relationshipObject->addResource($resource);
		}
		
		if ($links !== []) {
			$relationshipObject->setLinksObject(LinksObject::fromArray($links));
		}
		if ($meta !== []) {
			$relationshipObject->setMetaObject(MetaObject::fromArray($meta));
		}
		
		return $relationshipObject;
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addMeta($key, $value) {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * define the key used when adding the LinkObject to the LinksObject
	 * 
	 * @param  string $key
	 */
	public function defineKey($key) {
		Validator::checkMemberName($key);
		
		$this->key = $key;
	}
	
	/**
	 * whether or not the $otherResource is (one of) the resource(s) inside the relationship
	 * 
	 * @param  ResourceInterface $otherResource
	 * @return boolean
	 */
	public function hasResource(ResourceInterface $otherResource) {
		if ($this->type === RelationshipObject::TO_ONE) {
			return $this->resource->getResource()->equals($otherResource->getResource());
		}
		if ($this->type === RelationshipObject::TO_MANY) {
			foreach ($this->resources as $ownResource) {
				if ($ownResource->getResource()->equals($otherResource->getResource())) {
					return true;
				}
			}
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * set the resource on a to-one relationship
	 * 
	 * @param ResourceInterface $resource
	 * 
	 * @throws InputException if used on a to-many relationship, use {@see ->addResource()} instead
	 */
	public function setResource(ResourceInterface $resource) {
		if ($this->type === RelationshipObject::TO_MANY) {
			throw new InputException('can not set a resource on a to-many relationship, use ->addResource()');
		}
		
		$this->resource = $resource;
	}
	
	/**
	 * add a resource to a to-many relationship
	 * 
	 * @param ResourceInterface $resource
	 * 
	 * @throws InputException if used on a to-one relationship, use {@see ->setResource()} instead
	 */
	public function addResource(ResourceInterface $resource) {
		if ($this->type === RelationshipObject::TO_ONE) {
			throw new InputException('can not add a resource to a to-one relationship, use ->setResource()');
		}
		
		$this->resources[] = $resource;
	}
	
	/**
	 * @param LinkObject $linkObject
	 * @param string     $key        optional, required if $linkObject has no key defined
	 */
	public function addLinkObject(LinkObject $linkObject, $key=null) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->addLinkObject($linkObject, $key);
	}
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if ($this->type === RelationshipObject::TO_ONE && $this->resource !== null) {
			return false;
		}
		if ($this->type === RelationshipObject::TO_MANY && $this->resources !== []) {
			return false;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->type === RelationshipObject::TO_ONE && $this->resource !== null) {
			$array['data'] = $this->resource->getResource($identifierOnly=true)->toArray();
		}
		if ($this->type === RelationshipObject::TO_MANY && $this->resources !== []) {
			foreach ($this->resources as $resource) {
				$array['data'][] = $resource->getResource($identifierOnly=true)->toArray();
			}
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
