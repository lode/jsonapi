<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;

class ResourceObject extends ResourceIdentifierObject {
	/** @var AttributesObject */
	public $attributes;
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
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		
		return $array;
	}
}
