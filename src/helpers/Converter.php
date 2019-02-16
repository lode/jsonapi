<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;

/**
 * @internal
 */
class Converter {
	/**
	 * @param  object $object
	 * @return array
	 */
	public static function objectToArray($object) {
		if ($object instanceof ObjectInterface) {
			return $object->toArray();
		}
		
		return get_object_vars($object);
	}
	
	/**
	 * @see https://stackoverflow.com/questions/7593969/regex-to-split-camelcase-or-titlecase-advanced/7599674#7599674
	 * 
	 * @param  string $camelCase
	 * @return string
	 */
	public static function camelCaseToWords($camelCase) {
		$parts = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $camelCase);
		
		return implode(' ', $parts);
	}
}
