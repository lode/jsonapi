<?php

namespace alsvanzelf\jsonapiTests\example_output\relationship_to_many_document;

use alsvanzelf\jsonapi\CollectionDocument;

class relationship_to_many_document {
	public static function createJsonapiDocument() {
		$document = new CollectionDocument();
		$document->add('tags', 2);
		$document->add('tags', 3);
		
		$document->setSelfLink('/articles/1/relationship/tags');
		$document->addLink('related', '/articles/1/tags');
		
		return $document;
	}
}
