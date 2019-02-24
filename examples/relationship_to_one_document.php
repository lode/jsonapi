<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;

require 'bootstrap_examples.php';

/**
 * a to-one relationship response
 */

$relationshipDocument = new ResourceDocument('author', 12);

$relationshipDocument->setSelfLink('/articles/1/relationship/author', $meta=[], $level=Document::LEVEL_ROOT);
$relationshipDocument->addLink('related', '/articles/1/author');

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$relationshipDocument->toJson($options);
