<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\helpers\LinksManager;

/**
 * using LinksManager to make it non-trait to test against it
 */
class TestableNonTraitLinksManager {
	use LinksManager;
	
	public function toArray() {
		if ($this->links === null) {
			return [];
		}
		
		return $this->links->toArray();
	}
}
