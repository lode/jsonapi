<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;

interface HasLinksInterface {
	/**
	 * set a key containing a link
	 * 
	 * if $meta is given, a LinkObject is added, otherwise a link string is added
	 * 
	 * @param array<string, mixed> $meta
	 */
	public function addLink(string $key, ?string $href, array $meta=[]): void;
	
	/**
	 * set a key containing a LinkObject
	 */
	public function addLinkObject(string $key, LinkObject $linkObject): void;
	
	/**
	 * set a LinksObject containing all links
	 */
	public function setLinksObject(LinksObject $linksObject): void;
}
