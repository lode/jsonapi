<?php

use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\extensions\AtomicOperationsDocument;

require 'bootstrap_examples.php';

/**
 * use the atomic operations extension as extension to the document
 */

$document = new AtomicOperationsDocument();

$user1  = new ResourceObject('user', 1);
$user2  = new ResourceObject('user', 2);
$user42 = new ResourceObject('user', 42);

$user1->add('name',  'Ford');
$user2->add('name',  'Arthur');
$user42->add('name', 'Zaphod');

$document->addResults($user1);
$document->addResults($user2);
$document->addResults($user42);

/**
 * get the json
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
