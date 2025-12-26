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
	 * @param string $key
	 * @param string $href
	 */
	public function addLink($key, $href, array $meta=[]);
	
	/**
	 * set a key containing a LinkObject
	 * 
	 * @param string $key
	 */
	public function addLinkObject($key, LinkObject $linkObject);
	
	/**
	 * set a LinksObject containing all links
	 */
	public function setLinksObject(LinksObject $linksObject);
}
