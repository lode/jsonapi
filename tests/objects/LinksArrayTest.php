<?php

namespace alsvanzelf\jsonapiTests\objects;

use alsvanzelf\jsonapi\objects\LinksArray;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated array links are not supported anymore
 */
class LinksArrayTest extends TestCase {
	public function testFromObject_HappyPath() {
		$object = new \stdClass();
		$object->foo = 'https://jsonapi.org';
		
		$linksArray = LinksArray::fromObject($object);
		
		$array = $linksArray->toArray();
		
		$this->assertCount(1, $array);
		$this->assertArrayHasKey(0, $array);
		$this->assertSame('https://jsonapi.org', $array[0]);
	}
}
