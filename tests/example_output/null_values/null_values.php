<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output\null_values;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\RelationshipTypeEnum;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;

class null_values {
	public static function createJsonapiDocument(): ResourceDocument {
		$document = new ResourceDocument('user', 42);
		
		$document->add('foo', null);
		$document->addMeta('foo', null);
		
		$document->addLink('foo', null);
		$document->addLinkObject('bar', new LinkObject());
		
		$document->addRelationship('bar', null);
		$document->addRelationshipObject('baz', new RelationshipObject(RelationshipTypeEnum::ToOne));
		$document->addRelationshipObject('baf', new RelationshipObject(RelationshipTypeEnum::ToMany));
		
		return $document;
	}
}
