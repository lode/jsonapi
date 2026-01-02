<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output\status_only;

use alsvanzelf\jsonapi\MetaDocument;

class status_only {
	public static function createJsonapiDocument(): MetaDocument {
		$document = new MetaDocument();
		$document->setHttpStatusCode(201);
		
		return $document;
	}
}
