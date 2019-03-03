<?php

use alsvanzelf\jsonapi\CollectionDocument;

require 'bootstrap_examples.php';

/**
 * a to-many relationship response
 */

$relationshipDocument = new CollectionDocument();
$relationshipDocument->add('tags', 2);
$relationshipDocument->add('tags', 3);

$relationshipDocument->setSelfLink('/articles/1/relationship/tags');
$relationshipDocument->addLink('related', '/articles/1/tags');

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$relationshipDocument->toJson($options);
