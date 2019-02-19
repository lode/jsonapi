<?php

namespace alsvanzelf\jsonapiTests\example_output\resource_links;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleUser;

class resource_links {
	public static function createJsonapiDocument() {
		$user42        = new ExampleUser(42);
		$user42->name  = 'Zaphod Beeblebrox';
		$user42->heads = 2;
		
		$document = ResourceDocument::fromObject($user42, $type='user', $user42->id);
		
		$selfResourceMeta = ['level' => Document::LEVEL_RESOURCE];
		$partnerMeta      = ['level' => Document::LEVEL_RESOURCE];
		$redirectMeta     = ['level' => Document::LEVEL_ROOT];
		
		$document->setSelfLink('/user/42',        $selfResourceMeta);
		$document->addLink('partner',  '/user/1', $partnerMeta,  $level=Document::LEVEL_RESOURCE);
		$document->addLink('redirect', '/login',  $redirectMeta, $level=Document::LEVEL_ROOT);
		
		return $document;
	}
}
