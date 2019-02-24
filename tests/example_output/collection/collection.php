<?php

namespace alsvanzelf\jsonapiTests\example_output\collection;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapiTests\example_output\ExampleUser;

class collection {
	public static function createJsonapiDocument() {
		$user1        = new ExampleUser(1);
		$user1->name  = 'Ford Prefect';
		$user1->heads = 1;
		
		$user2        = new ExampleUser(2);
		$user2->name  = 'Arthur Dent';
		$user2->heads = '1, but not always there';
		
		$user42        = new ExampleUser(42);
		$user42->name  = 'Zaphod Beeblebrox';
		$user42->heads = 2;
		
		$users = [$user1, $user2, $user42];
		
		$collection = [];
		
		foreach ($users as $user) {
			$resource = ResourceObject::fromObject($user, $type='user', $user->id);
			
			if ($user->id == 42) {
				$ship = new ResourceObject('ship', 5);
				$ship->add('name', 'Heart of Gold');
				$resource->addRelationship('ship', $ship);
			}
			
			$collection[] = $resource;
		}
		
		$document = CollectionDocument::fromResources(...$collection);
		
		return $document;
	}
}
