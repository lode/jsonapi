<?php

namespace alsvanzelf\jsonapiTests\example_output\meta_only;

use alsvanzelf\jsonapi\MetaDocument;

class meta_only {
	public static function createJsonapiDocument() {
		$document = new MetaDocument();
		$document->addMeta('foo', 'bar');
		
		return $document;
	}
}
