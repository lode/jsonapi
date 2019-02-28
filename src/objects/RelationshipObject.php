<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\AtMembers;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class RelationshipObject implements ObjectInterface, RecursiveResourceContainerInterface {
	use AtMembers;
	
	const TO_ONE  = 'one';
	const TO_MANY = 'many';
	
	/** @var LinksObject */
	protected $links;
	/** @var MetaObject */
	protected $meta;
	/** @var ResourceInterface */
	protected $resource;
	/** @var string one of the RelationshipObject::TO_* constants */
	protected $type;
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
	 * create a RelationshipObject from mixed input
	 * 
	 * @param  mixed  $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param  array  $links    optional
	 * @param  array  $meta     optional
	 * @return RelationshipObject
	 * 
	 * @throws InputException if $relation is not one of the supported formats
	 */
	public static function fromAnything($relation, array $links=[], array $meta=[]) {
		if (is_array($relation)) {
			$relation = CollectionDocument::fromResources(...$relation);
		}
		
		if ($relation instanceof ResourceInterface) {
			$relationshipObject = self::fromResource($relation, $links, $meta);
		}
		elseif ($relation instanceof CollectionDocument) {
			$relationshipObject = self::fromCollectionDocument($relation, $links, $meta);
		}
		elseif ($relation === null) {
			$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		}
		else {
			throw new InputException('unknown format of relation "'.gettype($relation).'"');
		}
		
		return $relationshipObject;
	}
	
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
		
		foreach ($collectionDocument->getContainedResources() as $resource) {
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
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setRelatedLink($href, array $meta=[]) {
		$this->addLink('related', $href, $meta);
	}
	
	/**
	 * @param string $previousHref optional
	 * @param string $nextHref     optional
	 * @param string $firstHref    optional
	 * @param string $lastHref     optional
	 * 
	 * @throws InputException if used on a to-one relationship
	 */
	public function setPaginationLinks($previousHref=null, $nextHref=null, $firstHref=null, $lastHref=null) {
		if ($this->type === RelationshipObject::TO_ONE) {
			throw new InputException('can not add pagination links to a to-one relationship');
		}
		
		if ($previousHref !== null) {
			$this->addLink('prev', $previousHref);
		}
		if ($nextHref !== null) {
			$this->addLink('next', $nextHref);
		}
		if ($firstHref !== null) {
			$this->addLink('first', $firstHref);
		}
		if ($lastHref !== null) {
			$this->addLink('last', $lastHref);
		}
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
	 * @param string     $key
	 * @param LinkObject $linkObject
	 */
	public function addLinkObject($key, LinkObject $linkObject) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->addLinkObject($key, $linkObject);
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
	 * internal api
	 */
	
	/**
	 * whether or not the $otherResource is (one of) the resource(s) inside the relationship
	 * 
	 * @internal
	 * 
	 * @param  ResourceInterface $otherResource
	 * @return boolean
	 */
	public function hasResource(ResourceInterface $otherResource) {
		if ($this->isEmpty()) {
			return false;
		}
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
		
		return false;
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
		if ($this->hasAtMembers()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = $this->getAtMembers();
		
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->type === RelationshipObject::TO_ONE) {
			$array['data'] = null;
			if ($this->resource !== null) {
				$array['data'] = $this->resource->getResource($identifierOnly=true)->toArray();
			}
		}
		if ($this->type === RelationshipObject::TO_MANY) {
			$array['data'] = [];
			foreach ($this->resources as $resource) {
				$array['data'][] = $resource->getResource($identifierOnly=true)->toArray();
			}
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getNestedContainedResourceObjects() {
		if ($this->isEmpty()) {
			return [];
		}
		
		$resources       = ($this->type === RelationshipObject::TO_ONE) ? [$this->resource] : $this->resources;
		$resourceObjects = [];
		
		foreach ($resources as $resource) {
			if ($resource->getResource() instanceof ResourceObject === false) {
				continue;
			}
			
			/** @var ResourceObject */
			$resourceObject = $resource->getResource();
			
			if ($resourceObject->hasIdentifierPropertiesOnly()) {
				continue;
			}
			
			$resourceObjects[] = $resourceObject;
			$resourceObjects   = array_merge($resourceObjects, $resourceObject->getNestedContainedResourceObjects());
		}
		
		return $resourceObjects;
	}
}
