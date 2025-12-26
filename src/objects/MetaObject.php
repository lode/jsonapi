<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\AbstractObject;

class MetaObject extends AbstractObject {
	/** @var array<array-key, mixed> */
	protected array $meta = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param array<array-key, mixed> $meta
	 */
	public static function fromArray(array $meta): self {
		$metaObject = new self();
		
		foreach ($meta as $key => $value) {
			$metaObject->add($key, $value);
		}
		
		return $metaObject;
	}
	
	public static function fromObject(object $meta): self {
		$array = Converter::objectToArray($meta);
		
		return self::fromArray($array);
	}
	
	/**
	 * spec api
	 */
	
	public function add(string $key, mixed $value): void {
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->meta[$key] = $value;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if ($this->meta !== []) {
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
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		
		return array_merge($array, $this->meta);
	}
}
