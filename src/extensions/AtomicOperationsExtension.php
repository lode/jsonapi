<?php

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
	
	/**
	 * @inheritDoc
	 */
	public function getOfficialLink() {
		return 'https://jsonapi.org/ext/atomic/';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getNamespace() {
		return 'atomic';
	}
}
