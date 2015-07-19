<?php

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well
 * @note exceptions only output file, line, trace if the display_errors directive is true
 *       you can tune it with that, or by setting jsonapi\base::$debug to false
 * @note echo'ing the exception has the same effect as using ->send_response()
 */

try {
	$http_status = \alsvanzelf\jsonapi\response::STATUS_NOT_FOUND;
	$friendly_message = 'We don\'t know this user.';
	$about_link = 'www.example.com/search';
	throw new \alsvanzelf\jsonapi\exception('unknown user', $http_status, $previous=null, $friendly_message, $about_link);
}
catch (Exception $e) {
	echo $e;
	exit;
}
