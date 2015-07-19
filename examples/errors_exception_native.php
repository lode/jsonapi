<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well
 * @note exceptions only output file, line, trace if the display_errors directive is true
 *       you can tune it with that, or by setting jsonapi\base::$debug to false
 */

try {
	throw new Exception('unknown user', jsonapi\response::STATUS_NOT_FOUND);
}
catch (Exception $e) {
	$jsonapi = new jsonapi\errors($e);
	$jsonapi->send_response();
}
