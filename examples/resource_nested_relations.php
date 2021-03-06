<?php

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

require 'bootstrap_examples.php';

$userEntity = ExampleDataset::getEntity('user', 42);

/**
 * preparing base data and nesting relationships
 */

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

$document = ResourceDocument::fromObject($userEntity, $type='user', $userEntity->id);
$document->addRelationship('ship', $ship);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
