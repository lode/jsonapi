<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksArray;
use alsvanzelf\jsonapi\objects\LinksObject;

interface HasLinksInterface {
	public function addLink($key, $href, array $meta=[]);
	public function appendLink($key, $href, array $meta=[]);
	public function addLinkObject($key, LinkObject $linkObject);
	public function addLinksArray($key, LinksArray $linksArray);
	public function appendLinkObject($key, LinkObject $linkObject);
	public function setLinksObject(LinksObject $linksObject);
}
