<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface PaginableInterface {
	public function setPaginationLinks(
		?string $previousHref=null,
		?string $nextHref=null,
		?string $firstHref=null,
		?string $lastHref=null,
	): void;
}
