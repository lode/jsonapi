<?php

namespace alsvanzelf\jsonapiTests\example_output\status_only;

use alsvanzelf\jsonapi\MetaDocument;

class status_only {
	public static function createJsonapiDocument() {
		$document = new MetaDocument();
		$document->setHttpStatusCode(201);
		
		return $document;
	}
}
