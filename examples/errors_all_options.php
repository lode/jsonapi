<?php

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

require 'bootstrap_examples.php';

/**
 * setting all options
 */

$errorHumanApi = new ErrorObject($genericCode='Invalid input', $genericTitle='Too much options', $specificDetails='Please, choose a bit less. Consult your ...', $specificAboutLink='https://www.example.com/explanation.html');

// mark the cause of the error
$errorSpecApi->blameJsonPointer($pointer='/data/attributes/title');
$errorSpecApi->blameQueryParameter($parameter='filter');
$errorSpecApi->blamePostData($postKey='title');

// an identifier useful for helpdesk purposes
$errorSpecApi->setUniqueIdentifier($id=42);

// add meta data as you would on a normal json response
$errorSpecApi->addMeta($key='foo', $value='bar');

// or as object
$metaObject = new \stdClass();
$metaObject->property = 'value';
$errorSpecApi->addMeta($key='object', $metaObject);

// the http status code
// @note it is better to set this on the jsonapi\errors object ..
//       .. as only a single one can be consumed by the browser
$errorSpecApi->setHttpStatusCode($httpStatusCode=404);

// if not set during construction, set them here
$errorSpecApi->setApplicationCode($genericCode='Invalid input');
$errorSpecApi->setHumanTitle($genericTitle='Too much options');
$errorSpecApi->setHumanDetails($specificDetails='Please, choose a bit less. Consult your ...');
$errorSpecApi->setAboutLink($specificAboutLink='https://www.example.com/explanation.html', ['foo'=>'bar']);
$errorSpecApi->setActionLink($actionLink='https://www.example.com/helpdesk.html', ['label'=>'Contact us']);

/**
 * prepare multiple error objects for the errors response
 */

$anotherError      = new ErrorObject('kiss', 'Error objects can be small and simple as well.');
$previousException = new Exception('something went wrong!', 501);
$someException     = new Exception('please don\'t throw things', 503, $previousException);

/**
 * building up the json response
 * 
 * you can pass the $error object to the constructor ..
 * .. or add multiple errors via ->addErrorObject() or ->addException()
 * 
 * @note exceptions will expose the exception code, and use them as http status code if valid
 *       message, file, line, trace will not not exposed, unless $options['exceptionExposeDetails'] is set to true
 * 
 * further you can force another http status code than what's in the errors
 */

$document = new ErrorsDocument($errorHumanApi);

$document->addErrorObject($errorSpecApi);
$document->addErrorObject($anotherError);
$document->addException($someException, $options=['exceptionExposeDetails'=>true]);
$document->add($genericCode='Authentication error', $genericTitle='Not logged in');
$document->addLink('redirect', '/login', ['label'=>'Log in']);

$document->setHttpStatusCode(400);

/**
 * sending the response
 */

$options = [
	'prettyPrint' => true,
];
echo '<pre>'.$document->toJson($options);
