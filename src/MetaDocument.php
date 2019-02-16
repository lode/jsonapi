<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\objects\MetaObject;

class MetaDocument extends Document {
	/**
	 * human api
	 */
	
	/**
	 * @param  array $meta
	 * @return MetaDocument
	 */
	public static function fromArray(array $meta) {
		$metaDocument = new self();
		$metaDocument->setMetaObject(MetaObject::fromArray($meta));
		
		return $metaDocument;
	}
	
	/**
	 * @param  object $meta
	 * @return MetaDocument
	 */
	public static function fromObject($meta) {
		$array = Converter::objectToArray($meta);
		
		return self::fromArray($array);
	}
	
	/**
	 * wrapper for Document::addMeta() to the primary data of this document available via `add()`
	 * 
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 */
	public function add($key, $value, $level=Document::LEVEL_ROOT) {
		return parent::addMeta($key, $value, $level);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		// force meta to be set, and be an object when converting to json
		if (isset($array['meta']) === false) {
			$array['meta'] = new \stdClass();
		}
		
		return $array;
	}
}
