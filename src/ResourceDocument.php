<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\Converter;
use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
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
	 * @param  array      $attributes
	 * @param  string     $type       optional
	 * @param  string|int $id         optional
	 * @param  array      $options    optional {@see ResourceDocument::$defaults}
	 * @return ResourceDocument
	 */
	public static function fromArray(array $attributes, $type=null, $id=null, array $options=[]) {
		$resourceDocument = new self();
		$resourceDocument->setPrimaryResource(ResourceObject::fromArray($attributes, $type, $id, $options), $options);
		
		return $resourceDocument;
	}
	
	/**
	 * @param  object     $attributes
	 * @param  string     $type       optional
	 * @param  string|int $id         optional
	 * @param  array      $options    optional {@see ResourceDocument::$defaults}
	 * @return ResourceDocument
	 */
	public static function fromObject($attributes, $type=null, $id=null, array $options=[]) {
		$array = Converter::objectToArray($attributes);
		
		return self::fromArray($array, $type, $id, $options);
	}
	
	/**
	 * add key-value pairs to the resource's attributes
	 * 
	 * @param string $key
	 * @param mixed  $value   objects will be converted using `get_object_vars()`
	 * @param array  $options optional {@see ResourceDocument::$defaults}
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
		
		if ($options['skipIncluding'] === false) {
			$this->addIncludedResourceObject(...$relationshipObject->getNestedContainedResourceObjects());
		}
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
	 * set the self link on the resource
	 * 
	 * @param string $href
	 * @param array  $meta optional
	 */
	public function setSelfLink($href, array $meta=[]) {
		$this->resource->setSelfLink($href, $meta);
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
	 * wrapping ResourceObject spec api
	 */
	
	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->resource->setType($type);
	}
	
	/**
	 * @param string|int $id will be casted to a string
	 */
	public function setId($id) {
		$this->resource->setId($id);
	}
	
	/**
	 * @param AttributesObject $attributesObject
	 * @param array            $options          optional {@see ResourceObject::$defaults}
	 */
	public function setAttributesObject(AttributesObject $attributesObject, array $options=[]) {
		$this->resource->setAttributesObject($attributesObject, $options);
	}
	
	/**
	 * add a RelationshipObject to the resource
	 * 
	 * adds included resources if found inside the RelationshipObject, unless $options['skipIncluding'] is set to true
	 * 
	 * @param string             $key
	 * @param RelationshipObject $relationshipObject
	 * @param array              $options            optional {@see ResourceDocument::$defaults}
	 */
	public function addRelationshipObject($key, RelationshipObject $relationshipObject, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$this->resource->addRelationshipObject($key, $relationshipObject);
		
		if ($options['skipIncluding'] === false) {
			$this->addIncludedResourceObject(...$relationshipObject->getNestedContainedResourceObjects());
		}
	}
	
	/**
	 * set the RelationshipsObject to the resource
	 * 
	 * adds included resources if found inside the RelationshipObjects inside the RelationshipsObject, unless $options['skipIncluding'] is set to true
	 * 
	 * @param RelationshipsObject $relationshipsObject
	 * @param array               $options             optional {@see ResourceDocument::$defaults}
	 */
	public function setRelationshipsObject(RelationshipsObject $relationshipsObject, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$this->resource->setRelationshipsObject($relationshipsObject);
		
		if ($options['skipIncluding'] === false) {
			$this->addIncludedResourceObject(...$relationshipsObject->getNestedContainedResourceObjects());
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
		
		if ($options['skipIncluding'] === false && $this->resource instanceof RecursiveResourceContainerInterface) {
			$this->addIncludedResourceObject(...$this->resource->getNestedContainedResourceObjects());
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
