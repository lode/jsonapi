<?php

class user {

private static $dataset = array(
	1 => array(
		'name'  => 'Ford Prefect',
		'heads' => 1,
	),
	2 => array(
		'name' => 'Arthur Dent',
		'heads' => '1, but not always there',
	),
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
	
	$this->unknown = null;
}

public static function find_all() {
	return array_values(self::$dataset);
}

public function get_current_location() {
	return 'Earth';
}

}
