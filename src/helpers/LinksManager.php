<?php

declare(strict_types=1);

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
	 * if $meta is given, a LinkObject is added, otherwise a link string is added
	 * 
	 * @param array<array-key, mixed> $meta
	 */
	public function addLink(string $key, ?string $href, array $meta=[]): void {
		$this->ensureLinksObject();
		$this->links->add($key, $href, $meta);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * set a key containing a LinkObject
	 */
	public function addLinkObject(string $key, LinkObject $linkObject): void {
		$this->ensureLinksObject();
		$this->links->addLinkObject($key, $linkObject);
	}
	
	/**
	 * set a LinksObject containing all links
	 */
	public function setLinksObject(LinksObject $linksObject): void {
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
