<?php

class user {

public $id    = 42;
public $name  = 'Zaphod Beeblebrox';
public $heads = 2;

private static $dataset = array(
	42 => array(
		'name'  => 'Zaphod Beeblebrox',
		'heads' => 2,
	),
);

public function __construct($id) {
	if (empty($id)) {
		return;
	}
	
	if (!isset(self::$dataset[$id])) {
		throw new Exception('sorry, we have a limited dataset');
	}
	
	$this->id = $id;
	
	$data = self::$dataset[$id];
	foreach ($data as $key => $value) {
		$this->$key = $value;
	}
}

public static function find_all() {
	return array_values(self::$dataset);
}

public function get_current_location() {
	return 'Earth';
}

}
