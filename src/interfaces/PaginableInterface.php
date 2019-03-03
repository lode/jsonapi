<?php

namespace alsvanzelf\jsonapi\interfaces;

interface PaginableInterface {
	/**
	 * @param string $previousHref optional
	 * @param string $nextHref     optional
	 * @param string $firstHref    optional
	 * @param string $lastHref     optional
	 */
	public function setPaginationLinks($previousHref=null, $nextHref=null, $firstHref=null, $lastHref=null);
}
