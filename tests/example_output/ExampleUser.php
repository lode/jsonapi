<?php

namespace alsvanzelf\jsonapiTests\example_output;

class ExampleUser {
	public $id;
	public $name;
	public $heads;
	public $unknown;
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	function getCurrentLocation() {
		return 'Earth';
	}
}
