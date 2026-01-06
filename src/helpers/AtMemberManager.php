<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;

trait AtMemberManager {
	/** @var array<string, mixed> */
	protected array $atMembers = [];
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	public function addAtMember(string $key, mixed $value): void {
		if (str_starts_with($key, '@')) {
			$key = substr($key, 1);
		}
		
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->atMembers['@'.$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 */
	public function hasAtMembers(): bool {
		return ($this->atMembers !== []);
	}
	
	/**
	 * @internal
	 * 
	 * @return array<string, mixed>
	 */
	public function getAtMembers(): array {
		return $this->atMembers;
	}
}
