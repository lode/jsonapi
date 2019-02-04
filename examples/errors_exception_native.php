<?php

use alsvanzelf\jsonapi\ErrorsDocument;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well, unless $options['exceptionSkipPrevious'] is set to true
 * @note exceptions will expose the exception code, and use them as http status code if valid
 *       message, file, line, trace will not not exposed, unless $options['exceptionExposeDetails'] is set to true
 */

try {
	throw new Exception('unknown user', 404);
}
catch (Exception $e) {
	$options = [
		'exceptionExposeDetails' => true, // defaults to false
		'exceptionSkipPrevious'  => false,
	];
	$jsonapi = ErrorsDocument::fromException($e, $options);
	
	$options = [
		'prettyPrint' => true,
	];
	echo '<pre>'.$jsonapi->toJson($options);
}
