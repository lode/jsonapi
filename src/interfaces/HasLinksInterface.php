<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;
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
	 * append a link to a key with an array of links
	 * 
	 * if $meta is given, a LinkObject is added, otherwise a link string is added
	 * 
	 * @deprecated array links are not supported anymore {@see ->addLink()}
	 * 
	 * @param string $key
	 * @param string $href
	 */
	public function appendLink($key, $href, array $meta=[]);
	
	/**
	 * set a key containing a LinkObject
	 * 
	 * @param string $key
	 */
	public function addLinkObject($key, LinkObject $linkObject);
	
	/**
	 * set a key containing a LinksArray
	 * 
	 * @deprecated array links are not supported anymore {@see ->addLinkObject()}
	 * 
	 * @param string $key
	 */
	public function addLinksArray($key, LinksArray $linksArray);
	
	/**
	 * append a LinkObject to a key with a LinksArray
	 * 
	 * @deprecated array links are not supported anymore {@see ->addLinkObject()}
	 * 
	 * @param string $key
	 */
	public function appendLinkObject($key, LinkObject $linkObject);
	
	/**
	 * set a LinksObject containing all links
	 */
	public function setLinksObject(LinksObject $linksObject);
}
