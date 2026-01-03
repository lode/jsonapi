<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\profiles;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\profiles\CursorPaginationProfile;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Profiles')]
final class CursorPaginationProfileTest extends TestCase {
	public function testSetLinks_HappyPath(): void {
		$profile          = new CursorPaginationProfile();
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?page[size]=10';
		$firstCursor      = 'bar';
		$lastCursor       = 'foo';
		
		$collection->applyProfile($profile);
		$profile->setLinks($collection, $baseOrCurrentUrl, $firstCursor, $lastCursor);
		
		$array = $collection->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(2, $array['links']);
		parent::assertArrayHasKey('prev', $array['links']);
		parent::assertArrayHasKey('next', $array['links']);
		parent::assertArrayHasKey('href', $array['links']['prev']);
		parent::assertArrayHasKey('href', $array['links']['next']);
		parent::assertSame('/people?page[size]=10&page[before]='.$firstCursor, $array['links']['prev']['href']);
		parent::assertSame('/people?page[size]=10&page[after]='.$lastCursor, $array['links']['next']['href']);
	}
	
	public function test_WithRelationship(): void {
		$profile  = new CursorPaginationProfile();
		$document = new ResourceDocument('test', 1);
		$document->applyProfile($profile);
		
		$person1  = new ResourceObject('person', 1);
		$person2  = new ResourceObject('person', 2);
		$person42 = new ResourceObject('person', 42);
		$profile->setCursor($person1, 'ford');
		$profile->setCursor($person2, 'arthur');
		$profile->setCursor($person42, 'zaphod');
		
		$baseOrCurrentUrl = '/people?page[size]=10';
		$firstCursor      = 'ford';
		$lastCursor       = 'zaphod';
		$exactTotal       = 3;
		$bestGuessTotal   = 10;
		
		$relationship = RelationshipObject::fromAnything([$person1, $person2, $person42]);
		$profile->setLinks($relationship, $baseOrCurrentUrl, $firstCursor, $lastCursor);
		$profile->setCount($relationship, $exactTotal, $bestGuessTotal);
		
		$document->addRelationshipObject('people', $relationship);
		
		$array = $document->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('relationships', $array['data']);
		parent::assertArrayHasKey('people', $array['data']['relationships']);
		
		// re-map nested arrays to variables to speed up phpstan
		// without it, this file takes 10 seconds (!) more to process
		
		$people = $array['data']['relationships']['people'];
		parent::assertArrayHasKey('links', $people);
		parent::assertArrayHasKey('data', $people);
		parent::assertArrayHasKey('meta', $people);
		parent::assertArrayHasKey('prev', $people['links']);
		parent::assertArrayHasKey('next', $people['links']);
		parent::assertArrayHasKey('page', $people['meta']);
		parent::assertArrayHasKey('href', $people['links']['prev']);
		parent::assertArrayHasKey('href', $people['links']['next']);
		
		$peopleMeta = $people['meta'];
		parent::assertArrayHasKey('total', $peopleMeta['page']);
		parent::assertArrayHasKey('estimatedTotal', $peopleMeta['page']);
		parent::assertArrayHasKey('bestGuess', $peopleMeta['page']['estimatedTotal']);
		parent::assertCount(3, $people['data']);
		
		$firstPerson = $people['data'][0];
		parent::assertArrayHasKey('meta', $firstPerson);
		parent::assertArrayHasKey('page', $firstPerson['meta']);
		parent::assertArrayHasKey('cursor', $firstPerson['meta']['page']);
	}
	
	public function testSetLinksFirstPage_HappyPath(): void {
		$profile          = new CursorPaginationProfile();
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?page[size]=10';
		$lastCursor       = 'foo';
		
		$collection->applyProfile($profile);
		$profile->setLinksFirstPage($collection, $baseOrCurrentUrl, $lastCursor);
		
		$array = $collection->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(2, $array['links']);
		parent::assertArrayHasKey('prev', $array['links']);
		parent::assertArrayHasKey('next', $array['links']);
		parent::assertNull($array['links']['prev']);
		parent::assertArrayHasKey('href', $array['links']['next']);
		parent::assertSame('/people?page[size]=10&page[after]='.$lastCursor, $array['links']['next']['href']);
	}
	
	public function testSetLinksLastPage_HappyPath(): void {
		$profile          = new CursorPaginationProfile();
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?page[size]=10';
		$firstCursor      = 'bar';
		
		$collection->applyProfile($profile);
		$profile->setLinksLastPage($collection, $baseOrCurrentUrl, $firstCursor);
		
		$array = $collection->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(2, $array['links']);
		parent::assertArrayHasKey('prev', $array['links']);
		parent::assertArrayHasKey('next', $array['links']);
		parent::assertArrayHasKey('href', $array['links']['prev']);
		parent::assertNull($array['links']['next']);
		parent::assertSame('/people?page[size]=10&page[before]='.$firstCursor, $array['links']['prev']['href']);
	}
	
	public function testSetCursor(): void {
		$profile          = new CursorPaginationProfile();
		$resourceDocument = new ResourceDocument('user', 42);
		
		$resourceDocument->applyProfile($profile);
		$profile->setCursor($resourceDocument, 'foo');
		
		$array = $resourceDocument->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('data', $array);
		parent::assertArrayHasKey('meta', $array['data']);
		parent::assertArrayHasKey('page', $array['data']['meta']);
		parent::assertArrayHasKey('cursor', $array['data']['meta']['page']);
		parent::assertSame('foo', $array['data']['meta']['page']['cursor']);
	}
	
	public function testSetPaginationLinkObjectsExplicitlyEmpty_HapptPath(): void {
		$profile    = new CursorPaginationProfile();
		$collection = new CollectionDocument();
		
		$collection->applyProfile($profile);
		$profile->setPaginationLinkObjectsExplicitlyEmpty($collection);
		
		$array = $collection->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('links', $array);
		parent::assertCount(2, $array['links']);
		parent::assertArrayHasKey('prev', $array['links']);
		parent::assertArrayHasKey('next', $array['links']);
		parent::assertNull($array['links']['prev']);
		parent::assertNull($array['links']['next']);
	}
	
	public function testSetPaginationMeta(): void {
		$profile          = new CursorPaginationProfile();
		$collection       = new CollectionDocument();
		$exactTotal       = 42;
		$bestGuessTotal   = 100;
		$rangeIsTruncated = true;
		
		$collection->applyProfile($profile);
		$profile->setPaginationMeta($collection, $exactTotal, $bestGuessTotal, $rangeIsTruncated);
		
		$array = $collection->toArray();
		
		parent::assertArrayHasKey('jsonapi', $array);
		parent::assertArrayHasKey('profile', $array['jsonapi']);
		parent::assertCount(1, $array['jsonapi']['profile']);
		parent::assertSame($profile->getOfficialLink(), $array['jsonapi']['profile'][0]);
		
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('page', $array['meta']);
		parent::assertArrayHasKey('total', $array['meta']['page']);
		parent::assertArrayHasKey('estimatedTotal', $array['meta']['page']);
		parent::assertArrayHasKey('bestGuess', $array['meta']['page']['estimatedTotal']);
		parent::assertArrayHasKey('rangeTruncated', $array['meta']['page']);
		parent::assertSame(42, $array['meta']['page']['total']);
		parent::assertSame(100, $array['meta']['page']['estimatedTotal']['bestGuess']);
		parent::assertTrue($array['meta']['page']['rangeTruncated']);
	}
	
	public function testGetUnsupportedSortErrorObject_HappyPath(): void {
		$profile         = new CursorPaginationProfile();
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getUnsupportedSortErrorObject($genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('status', $array);
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('title', $array);
		parent::assertArrayHasKey('detail', $array);
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('type', $array['links']);
		parent::assertArrayHasKey('source', $array);
		parent::assertArrayHasKey('parameter', $array['source']);
		parent::assertSame('400', $array['status']);
		parent::assertSame('Unsupported sort', $array['code']);
		parent::assertSame($genericTitle, $array['title']);
		parent::assertSame($specificDetails, $array['detail']);
		parent::assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/unsupported-sort', $array['links']['type']);
		parent::assertSame('sort', $array['source']['parameter']);
	}
	
	public function testGetMaxPageSizeExceededErrorObject_HappyPath(): void {
		$profile         = new CursorPaginationProfile();
		$maxSize         = 42;
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getMaxPageSizeExceededErrorObject($maxSize, $genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('status', $array);
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('title', $array);
		parent::assertArrayHasKey('detail', $array);
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('type', $array['links']);
		parent::assertArrayHasKey('source', $array);
		parent::assertArrayHasKey('parameter', $array['source']);
		parent::assertArrayHasKey('meta', $array);
		parent::assertArrayHasKey('page', $array['meta']);
		parent::assertArrayHasKey('maxSize', $array['meta']['page']);
		parent::assertSame('400', $array['status']);
		parent::assertSame('Max page size exceeded', $array['code']);
		parent::assertSame($genericTitle, $array['title']);
		parent::assertSame($specificDetails, $array['detail']);
		parent::assertSame('page[size]', $array['source']['parameter']);
		parent::assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/max-size-exceeded', $array['links']['type']);
		parent::assertSame(42, $array['meta']['page']['maxSize']);
	}
	
	public function testGetInvalidParameterValueErrorObject_HappyPath(): void {
		$profile         = new CursorPaginationProfile();
		$queryParameter  = 'page[size]';
		$typeLink        = 'https://jsonapi.org';
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getInvalidParameterValueErrorObject($queryParameter, $typeLink, $genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('status', $array);
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('title', $array);
		parent::assertArrayHasKey('detail', $array);
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('type', $array['links']);
		parent::assertArrayHasKey('source', $array);
		parent::assertArrayHasKey('parameter', $array['source']);
		parent::assertSame('400', $array['status']);
		parent::assertSame('Invalid parameter value', $array['code']);
		parent::assertSame($genericTitle, $array['title']);
		parent::assertSame($specificDetails, $array['detail']);
		parent::assertSame('page[size]', $array['source']['parameter']);
		parent::assertSame('https://jsonapi.org', $array['links']['type']);
	}
	
	public function testGetRangePaginationNotSupportedErrorObject_HappyPath(): void {
		$profile         = new CursorPaginationProfile();
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getRangePaginationNotSupportedErrorObject($genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		parent::assertArrayHasKey('status', $array);
		parent::assertArrayHasKey('code', $array);
		parent::assertArrayHasKey('title', $array);
		parent::assertArrayHasKey('detail', $array);
		parent::assertArrayHasKey('links', $array);
		parent::assertArrayHasKey('type', $array['links']);
		parent::assertSame('400', $array['status']);
		parent::assertSame('Range pagination not supported', $array['code']);
		parent::assertSame($genericTitle, $array['title']);
		parent::assertSame($specificDetails, $array['detail']);
		parent::assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/range-pagination-not-supported', $array['links']['type']);
	}
	
	public function testSetQueryParameter_HappyPath(): void {
		$profile = new CursorPaginationProfile();
		$method  = new \ReflectionMethod($profile, 'setQueryParameter');
		
		$url   = '/people?sort=x&page[size]=10&page[after]=foo';
		$key   = 'page[after]';
		$value = 'bar';
		
		$newUrl = $method->invoke($profile, $url, $key, $value);
		
		parent::assertSame('/people?sort=x&page[size]=10&page[after]=bar', $newUrl);
	}
	
	public function testSetQueryParameter_EncodedUrl(): void {
		$profile = new CursorPaginationProfile();
		$method  = new \ReflectionMethod($profile, 'setQueryParameter');
		
		$url   = '/people?sort=x&page%5Bsize%5D=10&page%5Bafter%5D=foo';
		$key   = 'page[after]';
		$value = 'bar';
		
		$newUrl = $method->invoke($profile, $url, $key, $value);
		
		parent::assertSame('/people?sort=x&page%5Bsize%5D=10&page%5Bafter%5D=bar', $newUrl);
	}
}
