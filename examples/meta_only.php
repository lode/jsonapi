<?php

use alsvanzelf\jsonapi\MetaDocument;

require 'bootstrap_examples.php';

/**
 * there are a few use-cases for sending meta-only responses
 * in such cases, use the MetaDocument
 * 
 * prefer to actually send out a resource, error or collection
 */

$document = new MetaDocument();
$document->addMeta('foo', 'bar');

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
