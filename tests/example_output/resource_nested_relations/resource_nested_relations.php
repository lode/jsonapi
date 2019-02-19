<?php

namespace alsvanzelf\jsonapiTests\example_output\resource_nested_relations;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapiTests\example_output\ExampleUser;

class resource_nested_relations {
	public static function createJsonapiDocument() {
		$user42        = new ExampleUser(42);
		$user42->name  = 'Zaphod Beeblebrox';
		$user42->heads = 2;
		
		$flap = new ResourceObject('flap', 1);
		$flap->add('color', 'orange');
		
		$wing = new ResourceObject('wing', 1);
		$wing->add('side', 'top');
		$wing->addRelationship('flap', $flap);
		
		$ship = new ResourceObject('ship', 5);
		$ship->add('name', 'Heart of Gold');
		$ship->addRelationship('wing', $wing);
		
		/**
		 * building up the json response
		 */
		
		$document = ResourceDocument::fromObject($user42, $type='user', $user42->id);
		$document->addRelationship('ship', $ship);
		
		return $document;
	}
}
