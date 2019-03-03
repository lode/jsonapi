<?php

namespace alsvanzelf\jsonapiTests\example_output\resource_document_identifier_only;

use alsvanzelf\jsonapi\ResourceDocument;

class resource_document_identifier_only {
	public static function createJsonapiDocument() {
		return new ResourceDocument('user', 42);
	}
}
