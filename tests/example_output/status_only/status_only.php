<?php

namespace alsvanzelf\jsonapiTests\example_output\status_only;

use alsvanzelf\jsonapi\DataDocument;

class status_only {
	public static function createJsonapiDocument() {
		$document = new DataDocument();
		$document->setHttpStatusCode(201);
		
		return $document;
	}
}
