<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\objects\LinkObject;
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
