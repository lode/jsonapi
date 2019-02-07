<?php

namespace alsvanzelf\jsonapiTests\example_output\meta_only;

use alsvanzelf\jsonapi\DataDocument;

class meta_only {
	public static function createJsonapiDocument() {
		$document = new DataDocument();
		$document->addMeta('foo', 'bar');
		
		return $document;
	}
}
