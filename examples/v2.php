<?php

use alsvanzelf\jsonapi\ResourceDocument;

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

echo '</pre>';
