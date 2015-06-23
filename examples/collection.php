<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * the collection you want to send out
 * 
 * normally, you'd fetch this from a database
 */

require 'dataset.php';

$users = array(
	new user(1),
	new user(2),
	new user(42),
);

$collection = array();

foreach ($users as $user) {
	$resource = new jsonapi\resource($type='user', $user->id);
	$resource->set_self_link('/user/'.$user->id);
	$resource->fill_data($user);
	
	if ($user->id == 42) {
		$ship = new jsonapi\resource('ship', 5);
		$ship->add_data('name', 'Heart of Gold');
		$ship->set_self_link('/ship/5');
		$resource->add_relation('ship', $ship);
	}
	
	$collection[] = $resource;
}

/**
 * building up the json response
 * 
 * you can set arrays, single data points, or whole objects
 * objects are converted into arrays using their public keys
 */

$jsonapi = new jsonapi\collection($type='user');

$jsonapi->fill_collection($collection);

/**
 * sending the response
 */

$jsonapi->send_response();
