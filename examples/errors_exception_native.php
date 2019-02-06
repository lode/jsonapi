<?php

use alsvanzelf\jsonapi\ErrorsDocument;

require 'bootstrap_examples.php';

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
	$document = ErrorsDocument::fromException($e, $options);
	
	$options = [
		'prettyPrint' => true,
	];
	echo '<pre>'.$document->toJson($options);
}
