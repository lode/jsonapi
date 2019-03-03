<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;

trait AtMemberManager {
	/** @var array */
	protected $atMembers = [];
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addAtMember($key, $value) {
		if (strpos($key, '@') === 0) {
			$key = substr($key, 1);
		}
		
		Validator::checkMemberName($key);
		
		if (is_object($value)) {
			$value = Converter::objectToArray($value);
		}
		
		$this->atMembers['@'.$key] = $value;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @return boolean
	 */
	public function hasAtMembers() {
		return ($this->atMembers !== []);
	}
	
	/**
	 * @internal
	 * 
	 * @return array
	 */
	public function getAtMembers() {
		return $this->atMembers;
	}
}
