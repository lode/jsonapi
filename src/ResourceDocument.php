<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class ResourceDocument extends DataDocument implements ResourceInterface {
	/** @var ResourceIdentifierObject|ResourceObject */
	public $resource;
	/** @var array */
	private static $defaults = [
		'skipIncluding' => false,
	];
	
	/**
	 * @note $type and $id are optional to pass during construction
	 *       however they are required for a valid ResourceDocument
	 *       so use ->setPrimaryResource() if not passing them during construction
	 * 
	 * @param string     $type optional
	 * @param string|int $id   optional
	 */
	public function __construct($type=null, $id=null) {
		parent::__construct();
		
		$this->setPrimaryResource(new ResourceObject($type, $id));
	}
	
	/**
	 * human api
	 */
	
	/**
	 * add key-value pairs to the resource's attributes
	 * 
	 * @param string $key
	 * @param mixed  $value   objects will be converted using `get_object_vars()`
	 * @param array  $options optional {@see ResourceObject::$defaults}
	 */
	public function add($key, $value, array $options=[]) {
		$this->resource->add($key, $value, $options);
	}
	
	/**
	 * add a relation to the resource
	 * 
	 * adds included resources if found inside the relation, unless $options['skipIncluding'] is set to true
	 * 
	 * @param string  $key
	 * @param mixed   $relation ResourceInterface | ResourceInterface[] | CollectionDocument
	 * @param array   $links    optional
	 * @param array   $meta     optional
	 * @param array   $options  optional {@see ResourceDocument::$defaults}
	 */
	public function addRelationship($key, $relation, array $links=[], array $meta=[], array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$relationshipObject = $this->resource->addRelationship($key, $relation, $links, $meta);
		
		if ($options['skipIncluding'] === false && $this->resource instanceof ResourceObject) {
			$this->addIncludedResourceObject(...$relationshipObject->getRelatedResourceObjects());
		}
	}
	
	/**
	 * @todo add to included resources, and allow skip that via parameter
	 * 
	 * @param RelationshipObject $relationshipObject
	 * @param string             $key                optional, required if $relationshipObject has no key defined
	 */
	public function addRelationshipObject(RelationshipObject $relationshipObject, $key=null) {
		$this->resource->addRelationshipObject($relationshipObject, $key);
	}
	
	/**
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 */
	public function addLink($key, $href, array $meta=[], $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_RESOURCE) {
			$this->resource->addLink($key, $href, $meta);
		}
		else {
			parent::addLink($key, $href, $meta, $level);
		}
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 */
	public function addMeta($key, $value, $level=Document::LEVEL_ROOT) {
		if ($level === Document::LEVEL_RESOURCE) {
			$this->resource->addMeta($key, $value);
		}
		else {
			parent::addMeta($key, $value, $level);
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * overwrites the primary resource
	 * 
	 * adds included resources if found inside the resource's relationships, unless $options['skipIncluding'] is set to true
	 * 
	 * @param ResourceInterface $resource
	 * @param array             $options  optional {@see ResourceDocument::$defaults}
	 * 
	 * @throws InputException if the $resource is a ResourceDocument itself
	 */
	public function setPrimaryResource(ResourceInterface $resource, array $options=[]) {
		if ($resource instanceof ResourceDocument) {
			throw new InputException('does not make sense to set a document inside a document, use ResourceObject or ResourceIdentifierObject instead');
		}
		
		$options = array_merge(self::$defaults, $options);
		
		$this->resource = $resource;
		
		if ($options['skipIncluding'] === false && $this->resource instanceof ResourceObject) {
			$this->addIncludedResourceObject(...$this->resource->getRelatedResourceObjects());
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		$array['data'] = null;
		if ($this->resource !== null && $this->resource->isEmpty() === false) {
			$array['data'] = $this->resource->toArray();
		}
		
		return $array;
	}
	
	/**
	 * ResourceInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function getResource($identifierOnly=false) {
		return $this->resource->getResource($identifierOnly);
	}
}
