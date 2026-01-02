<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output;

class ExampleUser {
	public ?string $name = null;
	public null|int|string $heads = null;
	public mixed $unknown = null;
	
	public function __construct(
		public int $id,
	) {}
	
	function getCurrentLocation(): string {
		return 'Earth';
	}
}
