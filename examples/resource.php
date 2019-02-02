<?php

use alsvanzelf\jsonapi\ResourceDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * the resource you want to send out
 * 
 * normally, you'd fetch this from a database
 */

require 'dataset.php';

$user = new user(42);

/**
 * building up the json response
 * 
 * you can set arrays, single data points, or whole objects
 * objects are converted into arrays using their public keys
 */

$jsonapi = ResourceDocument::fromObject($user, $type='user', $user->id);

$jsonapi->add('location', $user->get_current_location());

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$jsonapi->toJson($options);
