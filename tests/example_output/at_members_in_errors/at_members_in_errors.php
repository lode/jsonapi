<?php

namespace alsvanzelf\jsonapiTests\example_output\at_members_in_errors;

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\LinkObject;
use alsvanzelf\jsonapi\objects\LinksObject;
use alsvanzelf\jsonapi\objects\MetaObject;

class at_members_in_errors {
	public static function createJsonapiDocument() {
		/**
		 * root
		 */
		
		$document = new ErrorsDocument();
		$document->addAtMember('context', 'test');
		
		/**
		 * jsonapi
		 */
		
		$jsonapiObject = new JsonapiObject();
		$jsonapiObject->addAtMember('context', 'test');
		$document->setJsonapiObject($jsonapiObject);
		
		/**
		 * error
		 */
		
		$errorObject = new ErrorObject('generic code', 'generic title', 'specific details');
		$errorObject->addAtMember('context', 'test');
		
		/**
		 * error - source
		 * 
		 * @todo make it work to have @-members next to the sources
		 *       if we need it in relationship identifiers, it is worth adding it here as well
		 * @see https://github.com/json-api/json-api/issues/1367
		 */
		
		$errorObject->addSource('foo', 'bar');
		
		/**
		 * error - links
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		
		$linkObject = new LinkObject('https://jsonapi.org');
		$linkObject->addAtMember('context', 'test');
		$linkObject->setMetaObject($metaObject);
		
		$linksObject = new LinksObject();
		$linksObject->addAtMember('context', 'test');
		$linksObject->addLinkObject('foo', $linkObject);
		$errorObject->setLinksObject($linksObject);
		
		/**
		 * error - meta
		 */
		
		$metaObject = new MetaObject();
		$metaObject->addAtMember('context', 'test');
		$errorObject->setMetaObject($metaObject);
		
		$document->addErrorObject($errorObject);
		
		return $document;
	}
}
