<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface ObjectInterface {
	/**
	 * whether the object contains something for output
	 * 
	 * @internal
	 */
	public function isEmpty(): bool;
	
	/**
	 * generate array with the contents of the object
	 * 
	 * @internal
	 * 
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
}
