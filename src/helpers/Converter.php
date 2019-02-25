<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\LinkObject;

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
	
	/**
	 * generates the value for a content type header, with profiles merged in if available
	 * 
	 * @param  string             $contentType
	 * @param  ProfileInterface[] $profiles
	 * @return string
	 */
	public static function mergeProfilesInContentType($contentType, array $profiles) {
		if ($profiles === []) {
			return $contentType;
		}
		
		$profileLinks = [];
		foreach ($profiles as $profile) {
			$link = $profile->getAliasedLink();
			$profileLinks[] = ($link instanceof LinkObject) ? $link->toArray()['href'] : $link;
		}
		$profileLinks = implode(' ', $profileLinks);
		
		return $contentType.';profile="'.$profileLinks.'", '.$contentType;
	}
}
