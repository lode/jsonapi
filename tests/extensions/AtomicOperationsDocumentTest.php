<?php

namespace alsvanzelf\jsonapiTests\extensions;

use alsvanzelf\jsonapi\extensions\AtomicOperationsDocument;
use alsvanzelf\jsonapi\extensions\AtomicOperationsExtension;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

/**
 * @group Extensions
 */
class AtomicOperationsDocumentTest extends TestCase {
	public function testSetResults_HappyPath() {
		$document = new AtomicOperationsDocument();
		
		$resource1 = new ResourceObject('person', 1);
		$resource2 = new ResourceObject('person', 2);
		$resource3 = new ResourceObject('person', 3);
		$resource1->add('name', 'Ford');
		$resource2->add('name', 'Arthur');
		$resource3->add('name', 'Zaphod');
		$document->addResults($resource1, $resource2, $resource3);
		$document->setSelfLink('https://example.org/operations');
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('ext', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['ext']);
		$this->assertSame((new AtomicOperationsExtension())->getOfficialLink(), $array['jsonapi']['ext'][0]);
		
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('self', $array['links']);
		$this->assertArrayHasKey('href', $array['links']['self']);
		$this->assertArrayHasKey('type', $array['links']['self']);
		$this->assertSame('https://example.org/operations', $array['links']['self']['href']);
		$this->assertSame('application/vnd.api+json; ext="'.(new AtomicOperationsExtension())->getOfficialLink().'"', $array['links']['self']['type']);
		
		$this->assertArrayHasKey('atomic:results', $array);
		$this->assertCount(3, $array['atomic:results']);
		$this->assertSame(['data' => $resource1->toArray()], $array['atomic:results'][0]);
		$this->assertSame(['data' => $resource2->toArray()], $array['atomic:results'][1]);
		$this->assertSame(['data' => $resource3->toArray()], $array['atomic:results'][2]);
	}
	
	public function testSetResults_EmptySuccessResults() {
		$document = new AtomicOperationsDocument();
		$array    = $document->toArray();
		
		$this->assertArrayHasKey('jsonapi', $array);
		$this->assertArrayHasKey('ext', $array['jsonapi']);
		$this->assertCount(1, $array['jsonapi']['ext']);
		$this->assertSame((new AtomicOperationsExtension())->getOfficialLink(), $array['jsonapi']['ext'][0]);
		
		$this->assertArrayHasKey('atomic:results', $array);
		$this->assertCount(0, $array['atomic:results']);
	}
}
