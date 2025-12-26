<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\objects\MetaObject;

/**
 * this document can be used if neither data nor errors need to be set
 * meta can also be added to ResourceDocument, CollectionDocument and ErrorsDocument via `addMeta()`
 */
class MetaDocument extends Document {
	/**
	 * human api
	 */
	
	/**
	 * @param  array<string, mixed> $meta
	 */
	public static function fromArray(array $meta): self {
		$metaDocument = new self();
		$metaDocument->setMetaObject(MetaObject::fromArray($meta));
		
		return $metaDocument;
	}
	
	/**
	 * @param object $meta
	 */
	public static function fromObject(object $meta): self {
		$array = Converter::objectToArray($meta);
		
		return self::fromArray($array);
	}
	
	/**
	 * wrapper for Document::addMeta() to the primary data of this document available via `add()`
	 */
	public function add(string $key, mixed $value, DocumentLevelEnum $level=DocumentLevelEnum::Root): void {
		parent::addMeta($key, $value, $level);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray(): array {
		$array = parent::toArray();
		
		// force meta to be set, and be an object when converting to json
		if (isset($array['meta']) === false) {
			$array['meta'] = new \stdClass();
		}
		
		return $array;
	}
}
