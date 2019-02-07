<?php

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

require 'bootstrap_examples.php';

$user1  = ExampleDataset::getEntity('user', 1);
$user42 = ExampleDataset::getEntity('user', 42);

/**
 * send a single entity, called a resource, via the simple and human-friendly api
 * 
 * values you add can be arrays, single data points, or whole objects
 * objects are converted into arrays using their public keys
 */

$document = ResourceDocument::fromObject($user1, $type='user', $user1->id);
$document->add('location', $user1->getCurrentLocation());
$document->addLink('homepage', 'https://jsonapi.org');
$document->addMeta('difference', 'is in the code to generate this');

$relation = ResourceDocument::fromObject($user42, $type='user', $user42->id);
$document->addRelationship('friend', $relation);

/**
 * get the json
 * 
 * using $document->toJson() here for example purposes
 * use $document->sendResponse() to send directly
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options).'</pre>';
