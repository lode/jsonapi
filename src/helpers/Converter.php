<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;

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
	 * generates the value for a content type header, with extensions and profiles merged in if available
	 * 
	 * @param  string               $contentType
	 * @param  ExtensionInterface[] $extensions
	 * @param  ProfileInterface[]   $profiles
	 * @return string
	 */
	public static function prepareContentType($contentType, array $extensions, array $profiles) {
		if ($extensions !== []) {
			$extensionLinks = [];
			foreach ($extensions as $extension) {
				$extensionLinks[] = $extension->getOfficialLink();
			}
			$extensionLinks = implode(' ', $extensionLinks);
			
			$contentType .= '; ext="'.$extensionLinks.'"';
		}
		
		if ($profiles !== []) {
			$profileLinks = [];
			foreach ($profiles as $profile) {
				$profileLinks[] = $profile->getOfficialLink();
			}
			$profileLinks = implode(' ', $profileLinks);
			
			$contentType .= '; profile="'.$profileLinks.'"';
		}
		
		return $contentType;
	}
	
	/**
	 * @deprecated {@see prepareContentType()}
	 */
	public static function mergeProfilesInContentType($contentType, array $profiles) {
		return self::prepareContentType($contentType, $extensions=[], $profiles);
	}
}
