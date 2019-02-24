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
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function addLink($key, $href, array $meta=[]) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->add($key, $href, $meta);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param string     $key
	 * @param LinkObject $linkObject
	 */
	public function addLinkObject($key, LinkObject $linkObject) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->addLinkObject($key, $linkObject);
	}
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
}
