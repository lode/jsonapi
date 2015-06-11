<?php

require '../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(-1);
\alsvanzelf\jsonapi\base::$debug = true;

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well
 * @note exceptions only output file, line, trace if base::$debug is set to true
 */

try {
	throw new Exception('unknown user', 404);
}
catch (Exception $e) {
	$jsonapi = new \alsvanzelf\jsonapi\errors($e);
	$jsonapi->send_response();
}
