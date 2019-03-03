<?php

namespace alsvanzelf\jsonapiTests\example_output\errors_all_options;

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

class errors_all_options {
	public static function createJsonapiDocument() {
		$errorHumanApi = new ErrorObject($genericCode='Invalid input', $genericTitle='Too much options', $specificDetails='Please, choose a bit less. Consult your ...', $specificAboutLink='https://www.example.com/explanation.html');
		
		$errorSpecApi = new ErrorObject();
		$errorSpecApi->blameJsonPointer($pointer='/data/attributes/title');
		$errorSpecApi->blameQueryParameter($parameter='filter');
		$errorSpecApi->setUniqueIdentifier($id=42);
		$errorSpecApi->addMeta($key='foo', $value='bar');
		$errorSpecApi->setHttpStatusCode($httpStatusCode=404);
		$errorSpecApi->setApplicationCode($genericCode='Invalid input');
		$errorSpecApi->setHumanTitle($genericTitle='Too much options');
		$errorSpecApi->setHumanDetails($specificDetails='Please, choose a bit less. Consult your ...');
		$errorSpecApi->setAboutLink($specificAboutLink='https://www.example.com/explanation.html', ['foo'=>'bar']);
		
		$metaObject = new \stdClass();
		$metaObject->property = 'value';
		$errorSpecApi->addMeta($key='object', $metaObject);
		
		$anotherError      = new ErrorObject('kiss', 'Error objects can be small and simple as well.');
		$previousException = new \Exception('something went wrong!');
		$someException     = new \Exception('please don\'t throw things', 500, $previousException);
		
		$document = new ErrorsDocument($errorHumanApi);
		$document->addErrorObject($errorSpecApi);
		$document->addErrorObject($anotherError);
		$document->addException($someException, $options=['includeExceptionTrace' => false, 'stripExceptionBasePath' => __DIR__]);
		$document->add($genericCode='Authentication error', $genericTitle='Not logged in');
		$document->addLink('redirect', '/login', ['label'=>'Log in']);
		$document->setHttpStatusCode(400);
		
		return $document;
	}
}
