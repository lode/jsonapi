<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output\errors_all_options;

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

class errors_all_options {
	public static function createJsonapiDocument() {
		$errorHumanApi = new ErrorObject('Invalid input', 'Too much options', 'Please, choose a bit less. Consult your ...', 'https://www.example.com/explanation.html', 'https://www.example.com/documentation.html');
		
		$errorSpecApi = new ErrorObject();
		$errorSpecApi->blameJsonPointer('/data/attributes/title');
		$errorSpecApi->blameQueryParameter('filter');
		$errorSpecApi->blameHeader('X-Foo');
		$errorSpecApi->setUniqueIdentifier(42);
		$errorSpecApi->addMeta('foo', 'bar');
		$errorSpecApi->setHttpStatusCode(404);
		$errorSpecApi->setApplicationCode('Invalid input');
		$errorSpecApi->setHumanTitle('Too much options');
		$errorSpecApi->setHumanDetails('Please, choose a bit less. Consult your ...');
		$errorSpecApi->setAboutLink('https://www.example.com/explanation.html', ['foo'=>'bar']);
		$errorSpecApi->setTypeLink('https://www.example.com/documentation.html', ['foo'=>'bar']);
		
		$metaObject = new \stdClass();
		$metaObject->property = 'value';
		$errorSpecApi->addMeta('object', $metaObject);
		
		$anotherError      = new ErrorObject('kiss', 'Error objects can be small and simple as well.');
		$previousException = new \Exception('something went wrong!');
		$someException     = new \Exception('please don\'t throw things', 500, $previousException);
		
		$document = new ErrorsDocument($errorHumanApi);
		$document->addErrorObject($errorSpecApi);
		$document->addErrorObject($anotherError);
		$document->addException($someException, ['includeExceptionTrace' => false, 'stripExceptionBasePath' => __DIR__]);
		$document->add('Authentication error', 'Not logged in');
		$document->addLink('redirect', '/login', ['label'=>'Log in']);
		$document->setHttpStatusCode(400);
		
		return $document;
	}
}
