<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

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
$resource->addLink('linkAtRoot', 'https://foo.exampe.com/', $meta=['foo' => 'bar']);
$resource->addLink('linkAtResource', 'https://baf.exampe.com/', $meta=['foo' => 'bar'], Document::LEVEL_RESOURCE);
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
$error = new ErrorObject();
$error->addLink('linkAtError', 'https://error.exampe.com/');
$jsonapi->addErrorObject($error);
$jsonapi->addLink('linkAtRoot', 'https://root.exampe.com/');
$jsonapi->sendResponse();

echo '</pre>';
