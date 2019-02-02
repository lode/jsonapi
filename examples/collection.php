<?php

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

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
	$resource = ResourceObject::fromObject($user, $type='user', $user->id);
	
	if ($user->id == 42) {
		$ship = new ResourceObject('ship', 5);
		$ship->add('name', 'Heart of Gold');
		$resource->addRelationship('ship', $ship);
	}
	
	$collection[] = $resource;
}

/**
 * building up the json response
 * 
 * you can set arrays, single data points, or whole objects
 * objects are converted into arrays using their public keys
 */

$jsonapi = CollectionDocument::fromResources(...$collection);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$jsonapi->toJson($options);
