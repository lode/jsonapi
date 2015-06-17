<?php

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * via an exception
 * 
 * @note echo'ing the exception has the same effect as using ->send_response()
 */

try {
	$friendly_message = 'We don\'t know this user.';
	$about_link = 'www.example.com/search';
	throw new \alsvanzelf\jsonapi\exception('unknown user', 404, $previous=null, $friendly_message, $about_link);
}
catch (Exception $e) {
	echo $e;
	exit;
}
