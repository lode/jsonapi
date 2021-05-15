<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

trait ExtensionMemberManager {
	/** @var array */
	protected $extensionMembers = [];
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	/**
	 * @param ExtensionInterface $extension
	 * @param string             $key
	 * @param mixed              $value
	 */
	public function addExtensionMember(ExtensionInterface $extension, $key, $value) {
		$namespace = $extension->getNamespace();
		
		if (strpos($key, $namespace.':') === 0) {
			$key = substr($key, strlen($namespace.':'));
		}
		
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->extensionMembers[$namespace.':'.$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @return boolean
	 */
	public function hasExtensionMembers() {
		return ($this->extensionMembers !== []);
	}
	
	/**
	 * @internal
	 * 
	 * @return array
	 */
	public function getExtensionMembers() {
		return $this->extensionMembers;
	}
}
