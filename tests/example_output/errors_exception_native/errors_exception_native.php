<?php

namespace alsvanzelf\jsonapiTests\example_output\errors_exception_native;

use alsvanzelf\jsonapi\ErrorsDocument;

class errors_exception_native {
	public static function createJsonapiDocument() {
		$exception = new \Exception('unknown user', 404);
		$options = [
			'exceptionExposeTrace'   => false,
			'exceptionSkipPrevious'  => false,
			'exceptionStripBasePath' => __DIR__,
		];
		$document = ErrorsDocument::fromException($exception, $options);
		
		return $document;
	}
}
