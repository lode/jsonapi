<?php

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * normally you don't need to set a content type
 * however it can be handy for debugging
 */

$content_type = \alsvanzelf\jsonapi\base::CONTENT_TYPE_DEBUG;

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well
 */

try {
	throw new Exception('unknown user', \alsvanzelf\jsonapi\base::STATUS_NOT_FOUND);
}
catch (Exception $e) {
	$jsonapi = new \alsvanzelf\jsonapi\errors($e);
	$jsonapi->send_response($content_type);
}
