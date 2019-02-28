<?php

namespace alsvanzelf\jsonapiTests\example_output\null_values;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;

class null_values {
	public static function createJsonapiDocument() {
		$document = new ResourceDocument('user', 42);
		
		$document->add('foo', null);
		$document->addMeta('foo', null);
		
		$document->addLink('foo', null);
		$document->addLinkObject('bar', new LinkObject());
		
		$document->addRelationship('bar', null);
		$document->addRelationshipObject('baz', new RelationshipObject(RelationshipObject::TO_ONE));
		$document->addRelationshipObject('baf', new RelationshipObject(RelationshipObject::TO_MANY));
		
		return $document;
	}
}
