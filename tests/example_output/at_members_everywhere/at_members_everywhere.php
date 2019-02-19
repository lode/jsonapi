<?php

namespace alsvanzelf\jsonapiTests\example_output\at_members_everywhere;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\AttributesObject;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\RelationshipsObject;
use alsvanzelf\jsonapi\objects\ResourceIdentifierObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

class at_members_everywhere {
	public static function createJsonapiDocument() {
		/**
		 * root
		 */
		
		$document = new ResourceDocument('user', 42);
		$document->addAtMember('context', 'test');
		
		/**
		 * jsonapi
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		$jsonapiObject = new JsonapiObject();
		$jsonapiObject->addAtMember('context', 'test');
		$jsonapiObject->setMetaObject($metaObject);
		$document->setJsonapiObject($jsonapiObject);
		
		/**
		 * links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addAtMember('context', 'test');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', 'test');
		$linksObject->addLinkObject('foo', $linkObject);
		$document->setLinksObject($linksObject);
		
		/**
		 * meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		$document->setMetaObject($metaObject);
		
		/**
		 * resource
		 */
		
		/**
		 * resource - relationships
		 * 
		 * @todo make it work to have @-members in both the identifier and the resource parts
		 *       e.g. it is missing in the data of the first relationship (`data.relationships.foo.data.@context`)
		 *       whereas it does appear in the second relationship (`data.relationships.bar.data.@context`)
		 * @see https://github.com/json-api/json-api/issues/1367
		 */
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addAtMember('context', 'test');
		
		$attributesObject = new AttributesObject();
		$attributesObject->addAtMember('context', 'test');
		
		$resourceObject = new ResourceObject('user', 1);
		$resourceObject->addAtMember('context', 'test');
		$resourceObject->setAttributesObject($attributesObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', 'test');
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addAtMember('context', 'test');
		$relationshipObject->setResource($resourceObject);
		$relationshipObject->setLinksObject($linksObject);
		$relationshipObject->setMetaObject($metaObject);
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 2);
		$resourceIdentifierObject->addAtMember('context', 'test');
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addAtMember('context', 'test');
		$relationshipObject->setResource($resourceIdentifierObject);
		
		$relationshipsObject->addRelationshipObject('bar', $relationshipObject);
		
		/**
		 * resource - attributes
		 */
		
		$attributesObject = new AttributesObject();
		$attributesObject->addAtMember('context', 'test');
		
		/**
		 * resource - links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addAtMember('context', 'test');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', 'test');
		$linksObject->addLinkObject('foo', $linkObject);
		
		/**
		 * resource - meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		/**
		 * resource - resource
		 */
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addAtMember('context', 'test');
		$resourceObject->setAttributesObject($attributesObject);
		$resourceObject->setLinksObject($linksObject);
		$resourceObject->setMetaObject($metaObject);
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->setPrimaryResource($resourceObject);
		
		/**
		 * included
		 */
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addAtMember('context', 'test');
		
		$resourceObject = new ResourceObject('user', 3);
		$resourceObject->addAtMember('context', 'test');
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->addIncludedResourceObject($resourceObject);
		
		return $document;
	}
}
