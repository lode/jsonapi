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
		$document->addAtMember('context', '/@context');
		
		/**
		 * jsonapi
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/jsonapi/meta/@context');
		
		$jsonapiObject = new JsonapiObject();
		$jsonapiObject->addAtMember('context', '/jsonapi/@context');
		$jsonapiObject->setMetaObject($metaObject);
		$document->setJsonapiObject($jsonapiObject);
		
		/**
		 * links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/links/foo/meta/@context');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addAtMember('context', '/links/foo/@context');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', '/links/@context');
		$linksObject->addLinkObject('foo', $linkObject);
		$document->setLinksObject($linksObject);
		
		/**
		 * meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/meta/@context');
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
		$relationshipsObject->addAtMember('context', '/data/relationships/@context');
		
		$attributesObject = new AttributesObject();
		$attributesObject->addAtMember('context', '/included/0/attributes/@context');
		
		$resourceObject = new ResourceObject('user', 1);
		$resourceObject->addAtMember('context', '/included/0/@context');
		$resourceObject->setAttributesObject($attributesObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', '/data/relationships/foo/links/@context');
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/data/relationships/foo/meta/@context');
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addAtMember('context', '/data/relationships/foo/@context');
		$relationshipObject->setResource($resourceObject);
		$relationshipObject->setLinksObject($linksObject);
		$relationshipObject->setMetaObject($metaObject);
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 2);
		$resourceIdentifierObject->addAtMember('context', '/data/relationships/bar/data/@context');
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addAtMember('context', '/data/relationships/bar/@context');
		$relationshipObject->setResource($resourceIdentifierObject);
		
		$relationshipsObject->addRelationshipObject('bar', $relationshipObject);
		
		/**
		 * resource - attributes
		 */
		
		$attributesObject = new AttributesObject();
		$attributesObject->addAtMember('context', '/data/attributes/@context');
		
		/**
		 * resource - links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/data/links/foo/meta/@context');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addAtMember('context', '/data/links/foo/@context');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', '/data/links/@context');
		$linksObject->addLinkObject('foo', $linkObject);
		
		/**
		 * resource - meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', '/data/meta/@context');
		
		/**
		 * resource - resource
		 */
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addAtMember('context', '/data/@context');
		$resourceObject->setAttributesObject($attributesObject);
		$resourceObject->setLinksObject($linksObject);
		$resourceObject->setMetaObject($metaObject);
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->setPrimaryResource($resourceObject);
		
		/**
		 * included
		 */
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addAtMember('context', '/included/1/relationships/@context');
		
		$resourceObject = new ResourceObject('user', 3);
		$resourceObject->addAtMember('context', '/included/1/@context');
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->addIncludedResourceObject($resourceObject);
		
		return $document;
	}
}
