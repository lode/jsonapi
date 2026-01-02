<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;

/**
 * @internal
 */
class Converter {
	/**
	 * @return array<string, mixed>
	 */
	public static function objectToArray(object $object): array {
		if ($object instanceof ObjectInterface) {
			return $object->toArray();
		}
		
		return get_object_vars($object);
	}
	
	/**
	 * @see https://stackoverflow.com/questions/7593969/regex-to-split-camelcase-or-titlecase-advanced/7599674#7599674
	 */
	public static function camelCaseToWords(string $camelCase): string {
		$parts = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $camelCase);
		
		return implode(' ', $parts);
	}
	
	/**
	 * generates the value for a content type header, with extensions and profiles merged in if available
	 * 
	 * @param ExtensionInterface[] $extensions
	 * @param ProfileInterface[]   $profiles
	 */
	public static function prepareContentType(ContentTypeEnum $contentType, array $extensions, array $profiles): string {
		$contentType = $contentType->value;
		
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
}
