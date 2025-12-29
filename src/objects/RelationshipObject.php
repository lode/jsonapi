<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\interfaces\HasLinksInterface;
use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\interfaces\PaginableInterface;
use alsvanzelf\jsonapi\interfaces\RecursiveResourceContainerInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

/**
 * @phpstan-consistent-constructor
 * warn when an extending constructor changes the arguments
 * that might break the class since we use `new static()`
 */
class RelationshipObject extends AbstractObject implements PaginableInterface, RecursiveResourceContainerInterface, HasLinksInterface, HasMetaInterface {
	use LinksManager;
	
	protected MetaObject $meta;
	protected ResourceInterface $resource;
	/** @var ResourceInterface[] */
	protected array $resources = [];
	
	public function __construct(
		protected readonly RelationshipTypeEnum $type,
	) {}
	
	/**
	 * human api
	 */
	
	/**
	 * create a RelationshipObject from mixed input
	 * 
	 * @param  CollectionDocument|ResourceInterface|ResourceInterface[]|null $relation 
	 * @param  array<string, ?string>                                        $links
	 * @param  array<string, mixed>                                          $meta
	 */
	public static function fromAnything(
		array|CollectionDocument|ResourceInterface|null $relation,
		array $links=[],
		array $meta=[],
	): static {
		if (is_array($relation)) {
			$relation = CollectionDocument::fromResources(...$relation);
		}
		
		if ($relation instanceof ResourceInterface) {
			$relationshipObject = static::fromResource($relation, $links, $meta);
		}
		elseif ($relation instanceof CollectionDocument) {
			$relationshipObject = static::fromCollectionDocument($relation, $links, $meta);
		}
		elseif ($relation === null) {
			$relationshipObject = new RelationshipObject(RelationshipTypeEnum::ToOne);
		}
		
		return $relationshipObject;
	}
	
	/**
	 * @param array<string, ?string> $links
	 * @param array<string, mixed>   $meta
	 */
	public static function fromResource(
		ResourceInterface $resource,
		array $links=[],
		array $meta=[],
		RelationshipTypeEnum $type=RelationshipTypeEnum::ToOne,
	): static {
		$relationshipObject = new static($type);
		
		match ($type) {
			RelationshipTypeEnum::ToOne  => $relationshipObject->setResource($resource),
			RelationshipTypeEnum::ToMany => $relationshipObject->addResource($resource),
		};
		
		if ($links !== []) {
			$relationshipObject->setLinksObject(LinksObject::fromArray($links));
		}
		if ($meta !== []) {
			$relationshipObject->setMetaObject(MetaObject::fromArray($meta));
		}
		
		return $relationshipObject;
	}
	
	/**
	 * @param array<string, ?string> $links
	 * @param array<string, mixed>   $meta
	 */
	public static function fromCollectionDocument(CollectionDocument $collectionDocument, array $links=[], array $meta=[]): static {
		$relationshipObject = new static(RelationshipTypeEnum::ToMany);
		
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
	 * @param array $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setSelfLink(string $href, array $meta=[]): void {
		$this->addLink('self', $href, $meta);
	}
	
	/**
	 * @param array $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setRelatedLink(string $href, array $meta=[]): void {
		$this->addLink('related', $href, $meta);
	}
	
	/**
	 * @throws InputException if used on a to-one relationship
	 */
	public function setPaginationLinks(
		?string $previousHref=null,
		?string $nextHref=null,
		?string $firstHref=null,
		?string $lastHref=null,
	): void {
		if ($this->type === RelationshipTypeEnum::ToOne) {
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
	
	public function addMeta(string $key, mixed $value): void {
		if (isset($this->meta) === false) {
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
	 * @throws InputException if used on a to-many relationship, use {@see ->addResource()} instead
	 */
	public function setResource(ResourceInterface $resource): void {
		if ($this->type === RelationshipTypeEnum::ToMany) {
			throw new InputException('can not set a resource on a to-many relationship, use ->addResource()');
		}
		
		$this->resource = $resource;
	}
	
	/**
	 * add a resource to a to-many relationship
	 * 
	 * @throws InputException if used on a to-one relationship, use {@see ->setResource()} instead
	 */
	public function addResource(ResourceInterface $resource): void {
		if ($this->type === RelationshipTypeEnum::ToOne) {
			throw new InputException('can not add a resource to a to-one relationship, use ->setResource()');
		}
		
		$this->resources[] = $resource;
	}
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * whether or not the $otherResource is (one of) the resource(s) inside the relationship
	 * 
	 * @internal
	 */
	public function hasResource(ResourceInterface $otherResource): bool {
		if ($this->isEmpty()) {
			return false;
		}
		
		switch ($this->type) {
			case RelationshipTypeEnum::ToOne:
				return $this->resource->getResource()->equals($otherResource->getResource());
			
			case RelationshipTypeEnum::ToMany:
				foreach ($this->resources as $ownResource) {
					if ($ownResource->getResource()->equals($otherResource->getResource())) {
						return true;
					}
				}
				
				return false;
		}
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		$resourceIsNotEmpty = match ($this->type) {
			RelationshipTypeEnum::ToOne  => isset($this->resource),
			RelationshipTypeEnum::ToMany => $this->resources !== [],
		};
		if ($resourceIsNotEmpty) {
			return false;
		}
		
		if ($this->hasLinks()) {
			return false;
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
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
		
		if ($this->hasLinks()) {
			$array['links'] = $this->links->toArray();
		}
		
		switch ($this->type) {
			case RelationshipTypeEnum::ToOne:
				$array['data'] = null;
				if (isset($this->resource)) {
					$array['data'] = $this->resource->getResource(identifierOnly: true)->toArray();
				}
				break;
			
			case RelationshipTypeEnum::ToMany:
				$array['data'] = [];
				foreach ($this->resources as $resource) {
					$array['data'][] = $resource->getResource(identifierOnly: true)->toArray();
				}
				break;
		}
		
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * RecursiveResourceContainerInterface
	 */
	
	public function getNestedContainedResourceObjects(): array {
		if ($this->isEmpty()) {
			return [];
		}
		
		$resources = match ($this->type) {
			RelationshipTypeEnum::ToOne  => [$this->resource],
			RelationshipTypeEnum::ToMany => $this->resources,
		};
		
		$resourceObjects = [];
		
		foreach ($resources as $resource) {
			// @phpstan-ignore instanceof.alwaysTrue, identical.alwaysFalse (we _can_ have both ResourceObject and ResourceIdentifierObject here)
			if ($resource->getResource() instanceof ResourceObject === false) {
				continue;
			}
			
			/** @var ResourceObject */
			$resourceObject = $resource->getResource();
			
			if ($resourceObject->hasIdentifierPropertiesOnly()) {
				continue;
			}
			
			$resourceObjects[] = $resourceObject;
			$resourceObjects   = [...$resourceObjects, ...$resourceObject->getNestedContainedResourceObjects()];
		}
		
		return $resourceObjects;
	}
}
