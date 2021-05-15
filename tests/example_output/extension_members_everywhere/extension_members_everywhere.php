<?php

namespace alsvanzelf\jsonapiTests\example_output\extension_members_everywhere;

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
use alsvanzelf\jsonapiTests\example_output\ExampleEverywhereExtension;

class extension_members_everywhere {
	public static function createJsonapiDocument() {
		$extension = new ExampleEverywhereExtension();
		
		$document = new ResourceDocument('user', 42);
		$document->applyExtension($extension);
		
		/**
		 * root
		 */
		
		$document->addExtensionMember($extension, 'key', '/key');
		
		/**
		 * jsonapi
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/jsonapi/meta/key');
		
		$jsonapiObject = new JsonapiObject();
		$jsonapiObject->addExtensionMember($extension, 'key', '/jsonapi/key');
		$jsonapiObject->setMetaObject($metaObject);
		$document->setJsonapiObject($jsonapiObject);
		
		/**
		 * links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/links/foo/meta/key');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addExtensionMember($extension, 'key', '/links/foo/key');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addExtensionMember($extension, 'key', '/links/key');
		$linksObject->addLinkObject('foo', $linkObject);
		$document->setLinksObject($linksObject);
		
		/**
		 * meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/meta/key');
		$document->setMetaObject($metaObject);
		
		/**
		 * resource
		 */
		
		/**
		 * resource - relationships
		 * 
		 * @todo make it work to have extension members in both the identifier and the resource parts
		 *       e.g. it is missing in the data of the first relationship (`data.relationships.foo.data.key`)
		 *       whereas it does appear in the second relationship (`data.relationships.bar.data.key`)
		 * @see https://github.com/json-api/json-api/issues/1367
		 */
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addExtensionMember($extension, 'key', '/data/relationships/key');
		
		$attributesObject = new AttributesObject();
		$attributesObject->addExtensionMember($extension, 'key', '/included/0/attributes/key');
		
		$resourceObject = new ResourceObject('user', 1);
		$resourceObject->addExtensionMember($extension, 'key', '/included/0/key');
		$resourceObject->setAttributesObject($attributesObject);
		
		$linksObject = new LinksObject();
		$linksObject->addExtensionMember($extension, 'key', '/data/relationships/foo/links/key');
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/data/relationships/foo/meta/key');
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addExtensionMember($extension, 'key', '/data/relationships/foo/key');
		$relationshipObject->setResource($resourceObject);
		$relationshipObject->setLinksObject($linksObject);
		$relationshipObject->setMetaObject($metaObject);
		
		$relationshipsObject->addRelationshipObject('foo', $relationshipObject);
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/data/relationships/bar/data/meta/key');
		
		$resourceIdentifierObject = new ResourceIdentifierObject('user', 2);
		$resourceIdentifierObject->addExtensionMember($extension, 'key', '/data/relationships/bar/data/key');
		$resourceIdentifierObject->setMetaObject($metaObject);
		
		$relationshipObject = new RelationshipObject(RelationshipObject::TO_ONE);
		$relationshipObject->addExtensionMember($extension, 'key', '/data/relationships/bar/key');
		$relationshipObject->setResource($resourceIdentifierObject);
		
		$relationshipsObject->addRelationshipObject('bar', $relationshipObject);
		
		/**
		 * resource - attributes
		 */
		
		$attributesObject = new AttributesObject();
		$attributesObject->addExtensionMember($extension, 'key', '/data/attributes/key');
		
		/**
		 * resource - links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/data/links/foo/meta/key');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addExtensionMember($extension, 'key', '/data/links/foo/key');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addExtensionMember($extension, 'key', '/data/links/key');
		$linksObject->addLinkObject('foo', $linkObject);
		
		/**
		 * resource - meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addExtensionMember($extension, 'key', '/data/meta/key');
		
		/**
		 * resource - resource
		 */
		
		$resourceObject = new ResourceObject('user', 42);
		$resourceObject->addExtensionMember($extension, 'key', '/data/key');
		$resourceObject->setAttributesObject($attributesObject);
		$resourceObject->setLinksObject($linksObject);
		$resourceObject->setMetaObject($metaObject);
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->setPrimaryResource($resourceObject);
		
		/**
		 * included
		 */
		
		$relationshipsObject = new RelationshipsObject();
		$relationshipsObject->addExtensionMember($extension, 'key', '/included/1/relationships/key');
		
		$resourceObject = new ResourceObject('user', 3);
		$resourceObject->addExtensionMember($extension, 'key', '/included/1/key');
		$resourceObject->setRelationshipsObject($relationshipsObject);
		
		$document->addIncludedResourceObject($resourceObject);
		
		return $document;
	}
}
