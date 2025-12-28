<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\AbstractObject;

class AttributesObject extends AbstractObject {
	/** @var array<string, mixed> */
	protected array $attributes = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @note if an `id` is set inside $attributes, it is removed from there
	 *       it is common to find it inside, and not doing so will cause an exception
	 */
	public static function fromArray(array $attributes): self {
		unset($attributes['id']);
		
		$attributesObject = new self();
		
		foreach ($attributes as $key => $value) {
			$attributesObject->add($key, $value);
		}
		
		return $attributesObject;
	}
	
	public static function fromObject(object $attributes): self {
		$array = Converter::objectToArray($attributes);
		
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
		
		$this->attributes[$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @return string[]
	 */
	public function getKeys(): array {
		return array_keys($this->attributes);
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if ($this->attributes !== []) {
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
		
		return [...$array, ...$this->attributes];
	}
}
