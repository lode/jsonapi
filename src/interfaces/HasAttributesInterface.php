<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface HasAttributesInterface {
	/**
	 * add key-value pairs to attributes
	 * 
	 * @see ResourceObject::$defaults
	 * 
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addAttribute($key, $value, array $options=[]);
}
