<?php

namespace alsvanzelf\jsonapiTests\example_output\relationships;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class relationships {
	public static function createJsonapiDocument() {
		$document = new ResourceDocument('user', 1);
		
		$ship1Resource = new ResourceObject('ship', 24);
		$ship1Resource->add('foo', 'bar');
		
		$ship2Resource = new ResourceObject('ship', 42);
		$ship2Resource->add('bar', 'baz');
		
		$friend1Resource = new ResourceObject('user', 24);
		$friend1Resource->add('foo', 'bar');
		
		$friend2Resource = new ResourceObject('user', 42);
		$friend2Resource->add('bar', 'baz');
		
		$dockResource = new ResourceObject('dock', 3);
		$dockResource->add('bar', 'baf');
		
		/**
		 * to-one relationship
		 */
		
		$document->addRelationship('included-ship', $ship1Resource);
		
		/**
		 * to-one relationship, without included resource
		 */
		
		$options = ['skipIncluding' => true];
		$document->addRelationship('excluded-ship', $ship2Resource, $links=[], $meta=[], $options);
		
		/**
		 * to-many relationship, one-by-one
		 */
		
		$relationshipObject = new RelationshipObject($type=RelationshipObject::TO_MANY);
		$relationshipObject->addResource($friend1Resource);
		$relationshipObject->addResource($friend2Resource);
		
		$document->addRelationshipObject('one-by-one-friends', $relationshipObject);
		
		/**
		 * to-many relationship, all-at-once
		 */
		
		$friends = new CollectionDocument();
		$friends->addResource($friend1Resource);
		$friends->addResource($friend2Resource);
		
		$document->addRelationship('included-friends', $friends);
		
		/**
		 * to-many relationship, different types
		 */
		
		$relationshipObject = new RelationshipObject($type=RelationshipObject::TO_MANY);
		$relationshipObject->addResource($ship1Resource);
		$relationshipObject->addResource($dockResource);
		
		$document->addRelationshipObject('one-by-one-neighbours', $relationshipObject);
		
		return $document;
	}
}
