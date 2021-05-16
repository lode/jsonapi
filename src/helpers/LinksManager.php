<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;
use alsvanzelf\jsonapi\objects\LinksObject;

trait LinksManager {
	/** @var LinksObject */
	protected $links;
	
	/**
	 * human api
	 */
	
	/**
	 * set a key containing a link
	 * 
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function addLink($key, $href, array $meta=[]) {
		$this->ensureLinksObject();
		$this->links->add($key, $href, $meta);
	}
	
	/**
	 * append a link to a key with an array of links
	 * 
	 * @deprecated array links are not supported anymore {@see addLink()}
	 * 
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function appendLink($key, $href, array $meta=[]) {
		$this->ensureLinksObject();
		$this->links->append($key, $href, $meta);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * set a key containing a LinkObject
	 * 
	 * @param string     $key
	 * @param LinkObject $linkObject
	 */
	public function addLinkObject($key, LinkObject $linkObject) {
		$this->ensureLinksObject();
		$this->links->addLinkObject($key, $linkObject);
	}
	
	/**
	 * set a key containing a LinksArray
	 * 
	 * @deprecated array links are not supported anymore {@see addLinkObject()}
	 * 
	 * @param string     $key
	 * @param LinksArray $linksArray
	 */
	public function addLinksArray($key, LinksArray $linksArray) {
		$this->ensureLinksObject();
		$this->links->addLinksArray($key, $linksArray);
	}
	
	/**
	 * append a LinkObject to a key with a LinksArray
	 * 
	 * @deprecated array links are not supported anymore {@see addLinkObject()}
	 * 
	 * @param string     $key
	 * @param LinkObject $linkObject
	 */
	public function appendLinkObject($key, LinkObject $linkObject) {
		$this->ensureLinksObject();
		$this->links->appendLinkObject($key, $linkObject);
	}
	
	/**
	 * set a LinksObject containing all links
	 * 
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 */
	private function ensureLinksObject() {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
	}
}
