<?php

use alsvanzelf\jsonapi\ErrorsDocument;

require 'bootstrap_examples.php';

/**
 * via an exception
 * 
 * @note previous exceptions will be added as well, unless $options['includeExceptionPrevious'] is set to false
 * @note exceptions will expose the exception message, code, file, line and trace
 *       also the code is used as http status code if valid
 */

try {
	throw new Exception('unknown user', 404);
}
catch (Exception $e) {
	$options = [
		'exceptionExposeTrace'   => true,
		'exceptionSkipPrevious'  => false,
	];
	$document = ErrorsDocument::fromException($e, $options);
	
	$options = [
		'prettyPrint' => true,
	];
	echo '<pre>'.$document->toJson($options);
}
