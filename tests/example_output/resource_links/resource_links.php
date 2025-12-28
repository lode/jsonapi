<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output\resource_links;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\enums\DocumentLevelEnum;
use alsvanzelf\jsonapiTests\example_output\ExampleUser;

class resource_links {
	public static function createJsonapiDocument() {
		$user42        = new ExampleUser(42);
		$user42->name  = 'Zaphod Beeblebrox';
		$user42->heads = 2;
		
		$document = ResourceDocument::fromObject($user42, 'user', $user42->id);
		
		$selfResourceMeta = ['level' => DocumentLevelEnum::Resource->name];
		$partnerMeta      = ['level' => DocumentLevelEnum::Resource->name];
		$redirectMeta     = ['level' => DocumentLevelEnum::Root->name];
		
		$document->setSelfLink('/user/42',        $selfResourceMeta);
		$document->addLink('partner',  '/user/1', $partnerMeta,  DocumentLevelEnum::Resource);
		$document->addLink('redirect', '/login',  $redirectMeta, DocumentLevelEnum::Root);
		
		return $document;
	}
}
