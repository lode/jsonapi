<?php

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

require 'bootstrap_examples.php';

/**
 * setting all options
 */

$error = new ErrorObject($genericCode='Invalid input', $genericTitle='Too much options', $specificDetails='Please, choose a bit less. Consult your ...', $specificAboutLink='https://www.example.com/explanation.html');

// mark the cause of the error
$error->blameJsonPointer($pointer='/data/attributes/title');
$error->blameQueryParameter($parameter='filter');
$error->blamePostData($postKey='title');

// an identifier useful for helpdesk purposes
$error->setUniqueIdentifier($id=42);

// add meta data as you would on a normal json response
$error->addMeta($key='foo', $value='bar');

// or as object
$metaObject = new StdClass();
$metaObject->property = 'value';
$error->addMeta($key='object', $metaObject);

// the http status code
// @note it is better to set this on the jsonapi\errors object ..
//       .. as only a single one can be consumed by the browser
$error->setHttpStatusCode($httpStatusCode=404);

// if not set during construction, set them here
$error->setApplicationCode($genericCode='Invalid input');
$error->setHumanTitle($genericTitle='Too much options');
$error->setHumanDetails($specificDetails='Please, choose a bit less. Consult your ...');
$error->setAboutLink($specificAboutLink='https://www.example.com/explanation.html', ['foo'=>'bar']);
$error->setActionLink($actionLink='https://www.example.com/helpdesk.html', ['label'=>'Contact us']);

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

$document = new ErrorsDocument($error);

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
