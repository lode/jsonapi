<?php

use alsvanzelf\jsonapi;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * send out a forbidden which you want to hide in public
 * 
 * @note try out setting the $debug flag and see the effect
 */

jsonapi\base::$debug = true;

try {
	throw new Exception('content is not from this user', jsonapi\response::STATUS_FORBIDDEN_HIDDEN);
}
catch (Exception $e) {
	$jsonapi = new jsonapi\errors($e);
	$jsonapi->add_meta('how-to', 'Toggle base::$debug to see it in effect');
	$jsonapi->send_response();
}
