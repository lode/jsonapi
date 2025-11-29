<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

interface HasExtensionMembersInterface {
	/**
	 * @param ExtensionInterface $extension
	 * @param string             $key
	 * @param mixed              $value
	 */
	public function addExtensionMember(ExtensionInterface $extension, $key, $value);
	
	/**
	 * @internal
	 * 
	 * @return boolean
	 */
	public function hasExtensionMembers();
	
	/**
	 * @internal
	 * 
	 * @return array
	 */
	public function getExtensionMembers();
}
