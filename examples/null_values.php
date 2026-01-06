<?php

declare(strict_types=1);

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;

require __DIR__.'/bootstrap_examples.php';

/**
 * tell that a value is non-existing
 */

$document = new ResourceDocument('user', 42);

// the easy ones where value is free format
$document->add('foo', null);
$document->addMeta('foo', null);

// show a specific link is not available
$document->addLink('foo', null);
$document->addLinkObject('bar', new LinkObject());

// show a relationship is not set
$document->addRelationship('bar', null);
$document->addRelationshipObject('baz', new RelationshipObject(RelationshipTypeEnum::ToOne));
$document->addRelationshipObject('baf', new RelationshipObject(RelationshipTypeEnum::ToMany));

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
