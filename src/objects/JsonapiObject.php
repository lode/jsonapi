<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class JsonapiObject implements ObjectInterface {
	/** @var string */
	public $version;
	/** @var MetaObject */
	public $meta;
	
	/**
	 * @param string $version one of the Document::JSONAPI_VERSION_* constants, optional, defaults to Document::JSONAPI_VERSION_DEFAULT
	 */
	public function __construct($version=Document::JSONAPI_VERSION_DEFAULT) {
		if ($version !== null) {
			$this->setVersion($version);
		}
	}
	
	/**
	 * human api
	 */
	
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
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
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
		if ($this->version !== null) {
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
		
		if ($this->version !== null) {
			$array['version'] = $this->version;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
