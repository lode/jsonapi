<?php

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * preparing base data and nesting relationships
 */

require 'dataset.php';

$user = new user(42);

$flap = new ResourceObject('flap', 1);
$flap->add('color', 'orange');

$wing = new ResourceObject('wing', 1);
$wing->add('side', 'top');
$wing->addRelationship('flap', $flap);

$ship = new ResourceObject('ship', 5);
$ship->add('name', 'Heart of Gold');
$ship->addRelationship('wing', $wing);

/**
 * building up the json response
 */

$jsonapi = ResourceDocument::fromObject($user, $type='user', $user->id);
$jsonapi->addRelationship('ship', $ship);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$jsonapi->toJson($options);
