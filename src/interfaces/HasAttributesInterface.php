<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface HasAttributesInterface {
	/**
	 * add key-value pairs to attributes
	 * 
	 * @see Validator::$validatorDefaults
	 * 
	 * @param PHPStanTypeAlias_Options_Validator $options
	 */
	public function addAttribute(string $key, mixed $value, array $options=[]): void;
}
