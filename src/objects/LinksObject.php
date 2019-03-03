<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;

class LinksObject implements ObjectInterface {
	use AtMemberManager;
	
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
	 * appends a link to an array of links under a specific key
	 * 
	 * @see LinksArray for use cases
	 * 
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 * 
	 * @throws DuplicateException if another link is already using that $key but is not an array
	 */
	public function append($key, $href, array $meta=[]) {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key]) === false) {
			$this->addLinksArray($key, new LinksArray());
		}
		elseif ($this->links[$key] instanceof LinksArray === false) {
			throw new DuplicateException('can not add to key "'.$key.'", it is not an array of links');
		}
		
		$this->links[$key]->add($href, $meta);
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
		
		$this->links[$key] = $href;
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
	 * @param string     $key
	 * @param LinksArray $linksArray
	 * 
	 * @throws DuplicateException if another link is already using that $key
	 */
	public function addLinksArray($key, LinksArray $linksArray) {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key])) {
			throw new DuplicateException('link with key "'.$key.'" already set');
		}
		
		$this->links[$key] = $linksArray;
	}
	
	/**
	 * @param  string     $key
	 * @param  LinkObject $linkObject
	 * 
	 * @throws DuplicateException if another link is already using that $key but is not an array
	 */
	public function appendLinkObject($key, LinkObject $linkObject) {
		Validator::checkMemberName($key);
		
		if (isset($this->links[$key]) === false) {
			$this->addLinksArray($key, new LinksArray());
		}
		elseif ($this->links[$key] instanceof LinksArray === false) {
			throw new DuplicateException('can not add to key "'.$key.'", it is not an array of links');
		}
		
		$this->links[$key]->addLinkObject($linkObject);
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		return ($this->links === [] && $this->hasAtMembers() === false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = $this->getAtMembers();
		
		foreach ($this->links as $key => $link) {
			if ($link instanceof LinkObject && $link->isEmpty() === false) {
				$array[$key] = $link->toArray();
			}
			elseif ($link instanceof LinksArray && $link->isEmpty() === false) {
				$array[$key] = $link->toArray();
			}
			elseif ($link instanceof LinkObject && $link->isEmpty()) {
				$array[$key] = null;
			}
			else { // string or null
				$array[$key] = $link;
			}
		}
		
		return $array;
	}
}
