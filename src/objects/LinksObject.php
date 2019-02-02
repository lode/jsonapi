<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Converter;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinkObject;

class LinksObject implements ObjectInterface {
	/** @var array with string|LinkObject */
	public $links = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  array  $links key-value with values being href strings
	 * @return LinksObject
	 */
	public static function fromArray(array $links) {
		$linksObject = new self();
		
		foreach ($links as $key => $href) {
			$linksObject->add($key, $href);
		}
		
		return $linksObject;
	}
	
	/**
	 * @param  object $links
	 * @return LinksObject
	 */
	public static function fromObject($links) {
		$array = Converter::objectToArray($links);
		
		return self::fromArray($array);
	}
	
	/**
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function add($key, $href, array $meta=[]) {
		if ($meta === []) {
			$this->addLinkString($key, $href);
		}
		else {
			$this->addLinkObject(new LinkObject($href, $meta), $key);
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $key
	 * @param string $href
	 */
	public function addLinkString($key, $href) {
		Validator::checkMemberName($key);
		
		$this->links[$key] =  $href;
	}
	
	/**
	 * @param LinkObject $linkObject
	 * @param string     $key        optional, required if $linkObject has no key defined
	 * 
	 * @throws InputException if $key is not given and $linkObject has no key defined
	 */
	public function addLinkObject(LinkObject $linkObject, $key=null) {
		if ($key === null && $linkObject->key === null) {
			throw new InputException('key not given nor defined inside the LinkObject');
		}
		elseif ($key === null) {
			$key = $linkObject->key;
		}
		else {
			Validator::checkMemberName($key);
		}
		
		$this->links[$key] = $linkObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return ($this->links === []);
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		foreach ($this->links as $key => $link) {
			if ($link instanceof LinkObject && $link->isEmpty() === false) {
				$array[$key] = $link->toArray();
			}
			else {
				$array[$key] = $link;
			}
		}
		
		return $array;
	}
}
