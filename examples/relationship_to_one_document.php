<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;

require 'bootstrap_examples.php';

/**
 * a to-one relationship response
 */

$relationshipDocument = new ResourceDocument('author', 12);

$relationshipDocument->setSelfLink('/articles/1/relationship/author', level: DocumentLevelEnum::Root);
$relationshipDocument->addLink('related', '/articles/1/author');

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$relationshipDocument->toJson($options);
