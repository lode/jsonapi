<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output\relationship_to_one_document;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;

class relationship_to_one_document {
	public static function createJsonapiDocument() {
		$document = new ResourceDocument('author', 12);
		
		$document->setSelfLink('/articles/1/relationship/author', level: DocumentLevelEnum::Root);
		$document->addLink('related', '/articles/1/author');
		
		return $document;
	}
}
