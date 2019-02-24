<?php

namespace alsvanzelf\jsonapiTests\example_output\errors_exception_native;

use alsvanzelf\jsonapi\ErrorsDocument;

class errors_exception_native {
	public static function createJsonapiDocument() {
		$exception = new \Exception('unknown user', 404);
		$options = [
			'exceptionExposeDetails' => false,
			'exceptionExposeTrace'   => false,
			'exceptionSkipPrevious'  => false,
		];
		$document = ErrorsDocument::fromException($exception, $options);
		
		return $document;
	}
}
