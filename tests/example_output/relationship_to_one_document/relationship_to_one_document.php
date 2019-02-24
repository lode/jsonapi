<?php

namespace alsvanzelf\jsonapiTests\example_output\relationship_to_one_document;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;

class relationship_to_one_document {
	public static function createJsonapiDocument() {
		$document = new ResourceDocument('author', 12);
		
		$document->setSelfLink('/articles/1/relationship/author', $meta=[], $level=Document::LEVEL_ROOT);
		$document->addLink('related', '/articles/1/author');
		
		return $document;
	}
}
