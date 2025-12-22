<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

trait ExtensionMemberManager {
	/** @var array<string, mixed> */
	protected array $extensionMembers = [];
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	public function addExtensionMember(ExtensionInterface $extension, string $key, mixed $value): void {
		$namespace = $extension->getNamespace();
		
		if (str_starts_with($key, $namespace.':')) {
			$key = substr($key, strlen($namespace.':'));
		}
		
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->extensionMembers[$namespace.':'.$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 */
	public function hasExtensionMembers(): bool {
		return ($this->extensionMembers !== []);
	}
	
	/**
	 * @internal
	 * 
	 * @return array<string, mixed>
	 */
	public function getExtensionMembers(): array {
		return $this->extensionMembers;
	}
}
