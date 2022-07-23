<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\helpers\Converter;

require 'bootstrap_examples.php';

/**
 * use a profile to define rules for members
 */

$profile = new ExampleTimestampsProfile();

$document = new ResourceDocument('user', 42);
$document->applyProfile($profile);

$document->add('foo', 'bar');

/**
 * you can apply the rules of the profile manually
 * or use methods of the profile if provided
 */

$created = new \DateTime('-1 year');
$updated = new \DateTime('-1 month');
$profile->setTimestamps($document, $created, $updated);

/**
 * get the json
 */

$contentType = Converter::prepareContentType(Document::CONTENT_TYPE_OFFICIAL, [], [$profile]);
echo '<code>Content-Type: '.$contentType.'</code>'.PHP_EOL;

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
