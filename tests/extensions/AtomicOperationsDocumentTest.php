<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\extensions;

use alsvanzelf\jsonapi\extensions\AtomicOperationsDocument;
use alsvanzelf\jsonapi\extensions\AtomicOperationsExtension;
use alsvanzelf\jsonapi\objects\ResourceObject;
use PHPUnit\Framework\TestCase;

/**
 * @group Extensions
 */
class AtomicOperationsDocumentTest extends TestCase {
	public function testSetResults_HappyPath(): void {
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
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('ext', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['ext']);
		parent::assertSame((new AtomicOperationsExtension())->getOfficialLink(), $array['jsonapi']['ext'][0]);
		
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('self', $array['links']);
		parent::assertArrayHasKey('href', $array['links']['self']);
		parent::assertArrayHasKey('type', $array['links']['self']);
		parent::assertSame('https://example.org/operations', $array['links']['self']['href']);
		parent::assertSame('application/vnd.api+json; ext="'.(new AtomicOperationsExtension())->getOfficialLink().'"', $array['links']['self']['type']);
		
		parent::assertArrayHasKey('atomic:results', $array);
		parent::assertCount(3, $array['atomic:results']);
		parent::assertSame(['data' => $resource1->toArray()], $array['atomic:results'][0]);
		parent::assertSame(['data' => $resource2->toArray()], $array['atomic:results'][1]);
		parent::assertSame(['data' => $resource3->toArray()], $array['atomic:results'][2]);
	}
	
	public function testSetResults_EmptySuccessResults(): void {
		$document = new AtomicOperationsDocument();
		$array    = $document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('ext', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['ext']);
		parent::assertSame((new AtomicOperationsExtension())->getOfficialLink(), $array['jsonapi']['ext'][0]);
		
		parent::assertArrayHasKey('atomic:results', $array);
		parent::assertCount(0, $array['atomic:results']);
	}
}
