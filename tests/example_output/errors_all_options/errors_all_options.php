<?php

namespace alsvanzelf\jsonapiTests\example_output\errors_all_options;

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

class errors_all_options {
	public static function createJsonapiDocument() {
		$error = new ErrorObject($genericCode='Invalid input', $genericTitle='Too much options', $specificDetails='Please, choose a bit less. Consult your ...', $specificAboutLink='https://www.example.com/explanation.html');
		
		$error->blameJsonPointer($pointer='/data/attributes/title');
		$error->blameQueryParameter($parameter='filter');
		$error->blamePostData($postKey='title');
		$error->setUniqueIdentifier($id=42);
		$error->addMeta($key='foo', $value='bar');
		$error->setHttpStatusCode($httpStatusCode=404);
		$error->setApplicationCode($genericCode='Invalid input');
		$error->setHumanTitle($genericTitle='Too much options');
		$error->setHumanDetails($specificDetails='Please, choose a bit less. Consult your ...');
		$error->setAboutLink($specificAboutLink='https://www.example.com/explanation.html', ['foo'=>'bar']);
		$error->setActionLink($actionLink='https://www.example.com/helpdesk.html', ['label'=>'Contact us']);
		
		$metaObject = new \stdClass();
		$metaObject->property = 'value';
		$error->addMeta($key='object', $metaObject);
		
		$anotherError      = new ErrorObject('kiss', 'Error objects can be small and simple as well.');
		$previousException = new \Exception('something went wrong!');
		$someException     = new \Exception('please don\'t throw things', 500, $previousException);
		
		$document = new ErrorsDocument($error);
		$document->addErrorObject($anotherError);
		$document->addException($someException, $options=['exceptionExposeDetails'=>false]);
		$document->add($genericCode='Authentication error', $genericTitle='Not logged in');
		$document->addLink('redirect', '/login', ['label'=>'Log in']);
		$document->setHttpStatusCode(400);
		
		return $document;
	}
}
