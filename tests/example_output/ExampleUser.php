<?php

namespace alsvanzelf\jsonapiTests\example_output;

class ExampleUser {
	public $name;
	public $heads;
	public $unknown;
	
	public function __construct(
		public $id,
	) {}
	
	function getCurrentLocation() {
		return 'Earth';
	}
}
