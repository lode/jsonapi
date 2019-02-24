<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinkObject;

class LinksObject implements ObjectInterface {
	/** @var array with string|LinkObject */
	protected $links = [];
	
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
			$this->addLinkObject($key, new LinkObject($href, $meta));
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $key
	 * @param string $href
	 * 
	 * @throws DuplicateException if another link is already using that $key
	 */
	public function addLinkString($key, $href) {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key])) {
			throw new DuplicateException('link with key "'.$key.'" already set');
		}
		
		$this->links[$key] =  $href;
	}
	
	/**
	 * @param string     $key
	 * @param LinkObject $linkObject
	 * 
	 * @throws DuplicateException if another link is already using that $key
	 */
	public function addLinkObject($key, LinkObject $linkObject) {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key])) {
			throw new DuplicateException('link with key "'.$key.'" already set');
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
