<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\extensions;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

/**
 * atomic operations provide a means to perform multiple "operations" in a linear and atomic manner
 * 
 * @see https://jsonapi.org/ext/atomic/
 * 
 * @see AtomicOperationsDocument
 */
class AtomicOperationsExtension implements ExtensionInterface {
	/**
	 * ExtensionInterface
	 */
	
	public function getOfficialLink(): string {
		return 'https://jsonapi.org/ext/atomic/';
	}
	
	public function getNamespace(): string {
		return 'atomic';
	}
}
