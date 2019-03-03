<?php

namespace alsvanzelf\jsonapiTests\example_output\resource_human_api;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapiTests\example_output\ExampleUser;

class resource_human_api {
	public static function createJsonapiDocument() {
		$user1        = new ExampleUser(1);
		$user1->name  = 'Ford Prefect';
		$user1->heads = 1;
		
		$user42        = new ExampleUser(42);
		$user42->name  = 'Zaphod Beeblebrox';
		$user42->heads = 2;
		
		$document = ResourceDocument::fromObject($user1, $type='user', $user1->id);
		$document->add('location', $user1->getCurrentLocation());
		$document->addLink('homepage', 'https://jsonapi.org');
		$document->addMeta('difference', 'is in the code to generate this');
		
		$relation = ResourceDocument::fromObject($user42, $type='user', $user42->id);
		$document->addRelationship('friend', $relation);
		
		return $document;
	}
}
