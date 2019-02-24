<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinkObject;

/**
 * an array of links (strings and LinkObjects), used for:
 * - type links in an ErrorObject
 * - profile links at root level
 */
class LinksArray implements ObjectInterface {
	/** @var array with string|LinkObject */
	protected $links = [];
	
	/**
	 * human api
	 */
	
	/**
	 * @param  string[] $hrefs
	 * @return LinksArray
	 */
	public static function fromArray(array $hrefs) {
		$linksArray = new self();
		
		foreach ($hrefs as $href) {
			$linksArray->add($href);
		}
		
		return $linksArray;
	}
	
	/**
	 * @param  object $hrefs
	 * @return LinksArray
	 */
	public static function fromObject($hrefs) {
		$array = Converter::objectToArray($hrefs);
		
		return self::fromArray($array);
	}
	
	/**
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function add($href, array $meta=[]) {
		if ($meta === []) {
			$this->addLinkString($href);
		}
		else {
			$this->addLinkObject(new LinkObject($href, $meta));
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string $href
	 */
	public function addLinkString($href) {
		$this->links[] = $href;
	}
	
	/**
	 * @param LinkObject $linkObject
	 */
	public function addLinkObject(LinkObject $linkObject) {
		$this->links[] = $linkObject;
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
		
		foreach ($this->links as $link) {
			if ($link instanceof LinkObject && $link->isEmpty() === false) {
				$array[] = $link->toArray();
			}
			elseif (is_string($link)) {
				$array[] = $link;
			}
		}
		
		return $array;
	}
}
