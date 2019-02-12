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
 * send a single entity, called a resource, via the specification-based api
 * 
 * @note this is the other extreme, it can be done in any way in between as well
 */

$attributes1 = new AttributesObject();
$attributes1->add('name', $user1->name);
$attributes1->add('heads', $user1->heads);
$attributes1->add('unknown', $user1->unknown);
$attributes1->add('location', $user1->getCurrentLocation());

$attributes42 = new AttributesObject();
$attributes42->add('name', $user42->name);
$attributes42->add('heads', $user42->heads);
$attributes42->add('unknown', $user42->unknown);

$links = new LinksObject();
$links->addLinkString('homepage', 'https://jsonapi.org');

$meta = new MetaObject();
$meta->add('difference', 'is in the code to generate this');

$resource = new ResourceObject();
$resource->setId($user42->id);
$resource->setType('user');
$resource->setAttributesObject($attributes42);

$relationship = new RelationshipObject(RelationshipObject::TO_ONE);
$relationship->setResource($resource);
$relationships = new RelationshipsObject();
$relationships->addRelationshipObject('friend', $relationship);

$document = new ResourceDocument();
$document->setId($user1->id);
$document->setType('user');
$document->setAttributesObject($attributes1);
$document->setLinksObject($links);
$document->setMetaObject($meta);
$document->setRelationshipsObject($relationships);

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
