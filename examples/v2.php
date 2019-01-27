<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\ErrorsDocument;

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
$resource->add($key, $value);
$resource->addMeta('metaAtRoot', 'foo');
$resource->addMeta('metaAtJsonapi', 'bar', Document::LEVEL_JSONAPI);
$resource->addMeta('metaAtResource', 'baf', Document::LEVEL_RESOURCE);
$resource->sendResponse();

echo '</pre><pre>';

$collection = new CollectionDocument($type);
$collection->add($type, ($id*2), $array);
$collection->addResource($resource);
$collection->sendResponse();

echo '</pre><pre>';

$jsonapi = new DataDocument();
$jsonapi->setHttpStatusCode(201);
$jsonapi->sendResponse();

echo '</pre><pre>';

$jsonapi = ErrorsDocument::fromException($exception);
$jsonapi->sendResponse();

echo '</pre>';
