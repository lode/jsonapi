<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\interfaces\ProfileInterface;
use alsvanzelf\jsonapi\objects\LinkObject;

class ProfileLinkObject extends LinkObject {
	/** @var array */
	protected $aliases = [];
	
	/**
	 * @param string $href
	 * @param array  $aliases optional
	 * @param array  $meta    optional
	 */
	public function __construct($href, array $aliases=[], array $meta=[]) {
		parent::__construct($href, $meta);
		
		$this->aliases = $aliases;
	}
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		if ($this->aliases !== []) {
			$array['aliases'] = $this->aliases;
		}
		
		return $array;
	}
}
