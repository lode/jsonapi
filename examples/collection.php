<?php

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

require 'bootstrap_examples.php';

$users = ExampleDataset::findEntities('user');

/**
 * send multiple entities, called a collection
 */

$collection = [];

foreach ($users as $user) {
	$resource = ResourceObject::fromObject($user, $type='user', $user->id);
	
	if ($user->id == 42) {
		$ship = new ResourceObject('ship', 5);
		$ship->add('name', 'Heart of Gold');
		$resource->addRelationship('ship', $ship);
	}
	
	$collection[] = $resource;
}

$document = CollectionDocument::fromResources(...$collection);

/**
 * get the json
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
