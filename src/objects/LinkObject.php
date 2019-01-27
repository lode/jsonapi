<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\MetaObject;

class LinkObject implements ObjectInterface {
	/** @var string */
	public $href;
	/** @var MetaObject */
	public $meta;
	/** @var string */
	public $key;
	
	/**
	 * @param string $href
	 * @param array  $meta optional
	 */
	public function __construct($href, array $meta=[]) {
		$this->setHref($href);
		
		if ($meta !== []) {
			$this->setMetaObject(MetaObject::fromArray($meta));
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
	 * define the key used when adding the LinkObject to the LinksObject
	 * 
	 * @param  string $key
	 */
	public function defineKey($key) {
		Validator::checkMemberName($key);
		
		$this->key = $key;
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $href
	 */
	public function setHref($href) {
		$this->href = $href;
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
		if ($this->href !== null) {
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
		$array = [
			'href' => $this->href,
		];
		
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
