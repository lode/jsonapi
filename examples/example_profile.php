<?php

use alsvanzelf\jsonapi\ResourceDocument;

require 'bootstrap_examples.php';

/**
 * use a profile as extension to the document
 */

$profile = new ExampleVersionProfile();

$document = new ResourceDocument('user', 42);
$document->applyProfile($profile);

/**
 * you can apply the rules of the profile manually
 * or use methods of the profile if provided
 */

$profile->setVersion($document, '2019');

/**
 * get the json
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
