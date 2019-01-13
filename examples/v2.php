<?php

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\DataDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

$type  = 'human';
$id    = 42;
$key   = 'foo';
$value = 'bar';
$array = [
	'baf' => 'baz',
];
$exception = new \Exception('foo', 422);

echo '<pre>';

$resource = new ResourceDocument($type, $id);
$resource->addData($key, $value);
$resource->sendResponse();

echo '</pre><pre>';

$collection = new CollectionDocument($type);
$collection->addData($type, ($id*2), $array);
$collection->addResource($resource);
$collection->sendResponse();

echo '</pre><pre>';

$jsonapi = new DataDocument();
$jsonapi->setHttpStatusCode(201);
$jsonapi->sendResponse();

echo '</pre>';
