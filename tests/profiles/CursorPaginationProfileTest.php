<?php

namespace alsvanzelf\jsonapiTests\profiles;

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\objects\RelationshipObject;
use alsvanzelf\jsonapi\objects\ResourceObject;
use alsvanzelf\jsonapi\profiles\CursorPaginationProfile;
use PHPUnit\Framework\TestCase;

/**
 * @group Profiles
 */
class CursorPaginationProfileTest extends TestCase {
	public function testSetLinks_HappyPath() {
		$profile          = new CursorPaginationProfile(['page' => 'pagination']);
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?'.$profile->getKeyword('page').'[size]=10';
		$firstCursor      = 'bar';
		$lastCursor       = 'foo';
		
		$profile->setLinks($collection, $baseOrCurrentUrl, $firstCursor, $lastCursor);
		
		$array = $collection->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(2, $array['links']);
		$this->assertArrayHasKey('prev', $array['links']);
		$this->assertArrayHasKey('next', $array['links']);
		$this->assertArrayHasKey('href', $array['links']['prev']);
		$this->assertArrayHasKey('href', $array['links']['next']);
		$this->assertSame('/people?'.$profile->getKeyword('page').'[size]=10&'.$profile->getKeyword('page').'[before]='.$firstCursor, $array['links']['prev']['href']);
		$this->assertSame('/people?'.$profile->getKeyword('page').'[size]=10&'.$profile->getKeyword('page').'[after]='.$lastCursor, $array['links']['next']['href']);
	}
	
	public function test_WithRelationship() {
		$profile  = new CursorPaginationProfile(['page' => 'pagination']);
		$document = new ResourceDocument('test', 1);
		
		$person1  = new ResourceObject('person', 1);
		$person2  = new ResourceObject('person', 2);
		$person42 = new ResourceObject('person', 42);
		$profile->setCursor($person1, 'ford');
		$profile->setCursor($person2, 'arthur');
		$profile->setCursor($person42, 'zaphod');
		
		$baseOrCurrentUrl = '/people?'.$profile->getKeyword('page').'[size]=10';
		$firstCursor      = 'ford';
		$lastCursor       = 'zaphod';
		$exactTotal       = 3;
		$bestGuessTotal   = 10;
		
		$relationship = RelationshipObject::fromAnything([$person1, $person2, $person42]);
		$profile->setLinks($relationship, $baseOrCurrentUrl, $firstCursor, $lastCursor);
		$profile->setCount($relationship, $exactTotal, $bestGuessTotal);
		
		$document->addRelationshipObject('people', $relationship);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('relationships', $array['data']);
		$this->assertArrayHasKey('people', $array['data']['relationships']);
		$this->assertArrayHasKey('links', $array['data']['relationships']['people']);
		$this->assertArrayHasKey('data', $array['data']['relationships']['people']);
		$this->assertArrayHasKey('meta', $array['data']['relationships']['people']);
		$this->assertArrayHasKey('prev', $array['data']['relationships']['people']['links']);
		$this->assertArrayHasKey('next', $array['data']['relationships']['people']['links']);
		$this->assertArrayHasKey('pagination', $array['data']['relationships']['people']['meta']);
		$this->assertArrayHasKey('href', $array['data']['relationships']['people']['links']['prev']);
		$this->assertArrayHasKey('href', $array['data']['relationships']['people']['links']['next']);
		$this->assertArrayHasKey('total', $array['data']['relationships']['people']['meta']['pagination']);
		$this->assertArrayHasKey('estimatedTotal', $array['data']['relationships']['people']['meta']['pagination']);
		$this->assertArrayHasKey('bestGuess', $array['data']['relationships']['people']['meta']['pagination']['estimatedTotal']);
		$this->assertCount(3, $array['data']['relationships']['people']['data']);
		$this->assertArrayHasKey('meta', $array['data']['relationships']['people']['data'][0]);
		$this->assertArrayHasKey('pagination', $array['data']['relationships']['people']['data'][0]['meta']);
		$this->assertArrayHasKey('cursor', $array['data']['relationships']['people']['data'][0]['meta']['pagination']);
	}
	
	public function testSetLinksFirstPage_HappyPath() {
		$profile          = new CursorPaginationProfile(['page' => 'pagination']);
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?'.$profile->getKeyword('page').'[size]=10';
		$lastCursor       = 'foo';
		
		$profile->setLinksFirstPage($collection, $baseOrCurrentUrl, $lastCursor);
		
		$array = $collection->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(2, $array['links']);
		$this->assertArrayHasKey('prev', $array['links']);
		$this->assertArrayHasKey('next', $array['links']);
		$this->assertNull($array['links']['prev']);
		$this->assertArrayHasKey('href', $array['links']['next']);
		$this->assertSame('/people?'.$profile->getKeyword('page').'[size]=10&'.$profile->getKeyword('page').'[after]='.$lastCursor, $array['links']['next']['href']);
	}
	
	public function testSetLinksLastPage_HappyPath() {
		$profile          = new CursorPaginationProfile(['page' => 'pagination']);
		$collection       = new CollectionDocument();
		$baseOrCurrentUrl = '/people?'.$profile->getKeyword('page').'[size]=10';
		$firstCursor      = 'bar';
		
		$profile->setLinksLastPage($collection, $baseOrCurrentUrl, $firstCursor);
		
		$array = $collection->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(2, $array['links']);
		$this->assertArrayHasKey('prev', $array['links']);
		$this->assertArrayHasKey('next', $array['links']);
		$this->assertArrayHasKey('href', $array['links']['prev']);
		$this->assertNull($array['links']['next']);
		$this->assertSame('/people?'.$profile->getKeyword('page').'[size]=10&'.$profile->getKeyword('page').'[before]='.$firstCursor, $array['links']['prev']['href']);
	}
	
	public function testSetCursor() {
		$profile          = new CursorPaginationProfile(['page' => 'pagination']);
		$resourceDocument = new ResourceDocument('user', 42);
		
		$profile->setCursor($resourceDocument, 'foo');
		
		$array = $resourceDocument->toArray();
		
		$this->assertArrayHasKey('data', $array);
		$this->assertArrayHasKey('meta', $array['data']);
		$this->assertArrayHasKey('pagination', $array['data']['meta']);
		$this->assertArrayHasKey('cursor', $array['data']['meta']['pagination']);
		$this->assertSame('foo', $array['data']['meta']['pagination']['cursor']);
	}
	
	public function testSetPaginationLinkObjectsExplicitlyEmpty_HapptPath() {
		$profile    = new CursorPaginationProfile(['page' => 'pagination']);
		$collection = new CollectionDocument();
		
		$profile->setPaginationLinkObjectsExplicitlyEmpty($collection);
		
		$array = $collection->toArray();
		
		$this->assertArrayHasKey('links', $array);
		$this->assertCount(2, $array['links']);
		$this->assertArrayHasKey('prev', $array['links']);
		$this->assertArrayHasKey('next', $array['links']);
		$this->assertNull($array['links']['prev']);
		$this->assertNull($array['links']['next']);
	}
	
	public function testSetPaginationMeta() {
		$profile          = new CursorPaginationProfile(['page' => 'pagination']);
		$collection       = new CollectionDocument();
		$exactTotal       = 42;
		$bestGuessTotal   = 100;
		$rangeIsTruncated = true;
		
		$profile->setPaginationMeta($collection, $exactTotal, $bestGuessTotal, $rangeIsTruncated);
		
		$array = $collection->toArray();
		
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('pagination', $array['meta']);
		$this->assertArrayHasKey('total', $array['meta']['pagination']);
		$this->assertArrayHasKey('estimatedTotal', $array['meta']['pagination']);
		$this->assertArrayHasKey('bestGuess', $array['meta']['pagination']['estimatedTotal']);
		$this->assertArrayHasKey('rangeTruncated', $array['meta']['pagination']);
		$this->assertSame(42, $array['meta']['pagination']['total']);
		$this->assertSame(100, $array['meta']['pagination']['estimatedTotal']['bestGuess']);
		$this->assertSame(true, $array['meta']['pagination']['rangeTruncated']);
	}
	
	public function testGetUnsupportedSortErrorObject_HappyPath() {
		$profile         = new CursorPaginationProfile(['page' => 'pagination']);
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getUnsupportedSortErrorObject($genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('status', $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('title', $array);
		$this->assertArrayHasKey('detail', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('type', $array['links']);
		$this->assertArrayHasKey('source', $array);
		$this->assertArrayHasKey('parameter', $array['source']);
		$this->assertCount(1, $array['links']['type']);
		$this->assertSame('400', $array['status']);
		$this->assertSame('Unsupported sort', $array['code']);
		$this->assertSame($genericTitle, $array['title']);
		$this->assertSame($specificDetails, $array['detail']);
		$this->assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/unsupported-sort', $array['links']['type'][0]);
		$this->assertSame('sort', $array['source']['parameter']);
	}
	
	public function testGetMaxPageSizeExceededErrorObject_HappyPath() {
		$profile         = new CursorPaginationProfile(['page' => 'pagination']);
		$maxSize         = 42;
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getMaxPageSizeExceededErrorObject($maxSize, $genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('status', $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('title', $array);
		$this->assertArrayHasKey('detail', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('type', $array['links']);
		$this->assertArrayHasKey('source', $array);
		$this->assertArrayHasKey('parameter', $array['source']);
		$this->assertArrayHasKey('meta', $array);
		$this->assertArrayHasKey('pagination', $array['meta']);
		$this->assertArrayHasKey('maxSize', $array['meta']['pagination']);
		$this->assertCount(1, $array['links']['type']);
		$this->assertSame('400', $array['status']);
		$this->assertSame('Max page size exceeded', $array['code']);
		$this->assertSame($genericTitle, $array['title']);
		$this->assertSame($specificDetails, $array['detail']);
		$this->assertSame('pagination[size]', $array['source']['parameter']);
		$this->assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/max-size-exceeded', $array['links']['type'][0]);
		$this->assertSame(42, $array['meta']['pagination']['maxSize']);
	}
	
	public function testGetInvalidParameterValueErrorObject_HappyPath() {
		$profile         = new CursorPaginationProfile(['page' => 'pagination']);
		$queryParameter  = 'pagination[size]';
		$typeLink        = 'https://jsonapi.org';
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getInvalidParameterValueErrorObject($queryParameter, $typeLink, $genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('status', $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('title', $array);
		$this->assertArrayHasKey('detail', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('type', $array['links']);
		$this->assertArrayHasKey('source', $array);
		$this->assertArrayHasKey('parameter', $array['source']);
		$this->assertCount(1, $array['links']['type']);
		$this->assertSame('400', $array['status']);
		$this->assertSame('Invalid parameter value', $array['code']);
		$this->assertSame($genericTitle, $array['title']);
		$this->assertSame($specificDetails, $array['detail']);
		$this->assertSame('pagination[size]', $array['source']['parameter']);
		$this->assertSame('https://jsonapi.org', $array['links']['type'][0]);
	}
	
	public function testGetRangePaginationNotSupportedErrorObject_HappyPath() {
		$profile         = new CursorPaginationProfile(['page' => 'pagination']);
		$genericTitle    = 'foo';
		$specificDetails = 'bar';
		
		$errorObject = $profile->getRangePaginationNotSupportedErrorObject($genericTitle, $specificDetails);
		
		$array = $errorObject->toArray();
		
		$this->assertArrayHasKey('status', $array);
		$this->assertArrayHasKey('code', $array);
		$this->assertArrayHasKey('title', $array);
		$this->assertArrayHasKey('detail', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('type', $array['links']);
		$this->assertCount(1, $array['links']['type']);
		$this->assertSame('400', $array['status']);
		$this->assertSame('Range pagination not supported', $array['code']);
		$this->assertSame($genericTitle, $array['title']);
		$this->assertSame($specificDetails, $array['detail']);
		$this->assertSame('https://jsonapi.org/profiles/ethanresnick/cursor-pagination/range-pagination-not-supported', $array['links']['type'][0]);
	}
	
	public function testSetQueryParameter_HappyPath() {
		$profile = new CursorPaginationProfile();
		$method  = new \ReflectionMethod($profile, 'setQueryParameter');
		$method->setAccessible(true);
		
		$url   = '/people?sort=x&page[size]=10&page[after]=foo';
		$key   = 'page[after]';
		$value = 'bar';
		
		$newUrl = $method->invoke($profile, $url, $key, $value);
		
		$this->assertSame('/people?sort=x&page[size]=10&page[after]=bar', $newUrl);
	}
	
	public function testSetQueryParameter_EncodedUrl() {
		$profile = new CursorPaginationProfile();
		$method  = new \ReflectionMethod($profile, 'setQueryParameter');
		$method->setAccessible(true);
		
		$url   = '/people?sort=x&page%5Bsize%5D=10&page%5Bafter%5D=foo';
		$key   = 'page[after]';
		$value = 'bar';
		
		$newUrl = $method->invoke($profile, $url, $key, $value);
		
		$this->assertSame('/people?sort=x&page%5Bsize%5D=10&page%5Bafter%5D=bar', $newUrl);
	}
}
