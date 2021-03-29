<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\RequestParser;
use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceRequestInterface;
use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceServerRequestInterface;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase {
	public function setUp() {
		$_SERVER['REQUEST_SCHEME'] = 'https';
		$_SERVER['HTTP_HOST']      = 'example.org';
		$_SERVER['REQUEST_URI']    = '/';
		$_SERVER['CONTENT_TYPE']   = Document::CONTENT_TYPE_OFFICIAL;
		
		$_GET  = [];
		$_POST = [];
	}
	
	public function testFromSuperglobals() {
		$_GET = [
			'include' => 'ship,ship.wing',
			'fields' => [
				'user' => 'name,location',
			],
			'sort' => 'name,-location',
			'page' => [
				'number' => '2',
				'size'   => '10',
			],
			'filter' => '42',
		];
		$_SERVER['REQUEST_URI'] = '/user/42?'.http_build_query($_GET);
		$_POST = [
			'data' => [
				'type'       => 'user',
				'id'         => '42',
				'attributes' => [
					'name' => 'Foo',
				],
				'relationships' => [
					'ship' => [
						'data' => [
							'type' => 'ship',
							'id'   => '42',
						],
					],
				],
			],
			'meta' => [
				'lock' => true,
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		
		$this->assertSame('https://example.org/user/42?'.http_build_query($_GET), $requestParser->getSelfLink());
		
		$this->assertTrue($requestParser->hasIncludePaths());
		$this->assertTrue($requestParser->hasSparseFieldset('user'));
		$this->assertTrue($requestParser->hasSortFields());
		$this->assertTrue($requestParser->hasPagination());
		$this->assertTrue($requestParser->hasFilter());
		
		$this->assertSame(['ship' => ['wing' => []]], $requestParser->getIncludePaths());
		$this->assertSame(['name', 'location'], $requestParser->getSparseFieldset('user'));
		$this->assertSame([['field' => 'name', 'order' => RequestParser::SORT_ASCENDING], ['field' => 'location', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
		$this->assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
		$this->assertSame('42', $requestParser->getFilter());
		
		$this->assertTrue($requestParser->hasAttribute('name'));
		$this->assertTrue($requestParser->hasRelationship('ship'));
		$this->assertTrue($requestParser->hasMeta('lock'));
		
		$this->assertSame('Foo', $requestParser->getAttribute('name'));
		$this->assertSame(['data' => ['type' => 'ship', 'id' => '42']], $requestParser->getRelationship('ship'));
		$this->assertSame(true, $requestParser->getMeta('lock'));
		
		$this->assertSame($_POST, $requestParser->getDocument());
	}
	
	public function testFromPsrRequest_WithRequestInterface() {
		$queryParameters = [
			'include' => 'ship,ship.wing',
			'fields' => [
				'user' => 'name,location',
			],
			'sort' => 'name,-location',
			'page' => [
				'number' => '2',
				'size'   => '10',
			],
			'filter' => '42',
		];
		$selfLink = 'https://example.org/user/42?'.http_build_query($queryParameters);
		$document = [
			'data' => [
				'type'       => 'user',
				'id'         => '42',
				'attributes' => [
					'name' => 'Foo',
				],
				'relationships' => [
					'ship' => [
						'data' => [
							'type' => 'ship',
							'id'   => '42',
						],
					],
				],
			],
			'meta' => [
				'lock' => true,
			],
		];
		
		$request = new TestableNonInterfaceRequestInterface($selfLink, $queryParameters, $document);
		$requestParser = RequestParser::fromPsrRequest($request);
		
		$this->assertSame('https://example.org/user/42?'.http_build_query($queryParameters), $requestParser->getSelfLink());
		
		$this->assertTrue($requestParser->hasIncludePaths());
		$this->assertTrue($requestParser->hasSparseFieldset('user'));
		$this->assertTrue($requestParser->hasSortFields());
		$this->assertTrue($requestParser->hasPagination());
		$this->assertTrue($requestParser->hasFilter());
		
		$this->assertSame(['ship' => ['wing' => []]], $requestParser->getIncludePaths());
		$this->assertSame(['name', 'location'], $requestParser->getSparseFieldset('user'));
		$this->assertSame([['field' => 'name', 'order' => RequestParser::SORT_ASCENDING], ['field' => 'location', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
		$this->assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
		$this->assertSame('42', $requestParser->getFilter());
		
		$this->assertTrue($requestParser->hasAttribute('name'));
		$this->assertTrue($requestParser->hasRelationship('ship'));
		$this->assertTrue($requestParser->hasMeta('lock'));
		
		$this->assertSame('Foo', $requestParser->getAttribute('name'));
		$this->assertSame(['data' => ['type' => 'ship', 'id' => '42']], $requestParser->getRelationship('ship'));
		$this->assertSame(true, $requestParser->getMeta('lock'));
		
		$this->assertSame($document, $requestParser->getDocument());
	}
	
	public function testFromPsrRequest_WithServerRequestInterface() {
		$queryParameters = [
			'sort' => 'name,-location',
		];
		$selfLink = 'https://example.org/user/42?'.http_build_query($queryParameters);
		$document = [];
		
		$request = new TestableNonInterfaceServerRequestInterface($selfLink, $queryParameters, $document);
		$requestParser = RequestParser::fromPsrRequest($request);
		
		$this->assertSame('https://example.org/user/42?'.http_build_query($queryParameters), $requestParser->getSelfLink());
		$this->assertTrue($requestParser->hasSortFields());
		$this->assertSame([['field' => 'name', 'order' => RequestParser::SORT_ASCENDING], ['field' => 'location', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
	}
	
	public function testGetSelfLink() {
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame('https://example.org/', $requestParser->getSelfLink());
		
		$_GET = ['foo' => 'bar'];
		$_SERVER['REQUEST_URI'] = '/user/42?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame('https://example.org/user/42?foo=bar', $requestParser->getSelfLink());
	}
	
	public function testHasIncludePaths() {
		$_GET = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasIncludePaths());
		
		$_GET = ['include' => 'foo,bar,baz.baf'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasIncludePaths());
	}
	
	public function testGetIncludePaths_Reformatted() {
		$_GET = ['include' => 'foo,bar,baz.baf'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame(['foo' => [], 'bar' => [], 'baz' => ['baf' => []]], $requestParser->getIncludePaths());
	}
	
	public function testGetIncludePaths_Raw() {
		$_GET = ['include' => 'foo,bar,baz.baf'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$options = ['useNestedIncludePaths' => false];
		$this->assertSame(['foo', 'bar', 'baz.baf'], $requestParser->getIncludePaths($options));
	}
	
	public function testHasSparseFieldset() {
		$_GET = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasSparseFieldset('foo'));
		
		$_GET = ['fields' => ['foo' => 'bar']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasSparseFieldset('foo'));
	}
	
	public function testGetSparseFieldset() {
		$_GET = ['fields' => ['foo' => 'bar,baz']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame(['bar', 'baz'], $requestParser->getSparseFieldset('foo'));
		
		$_GET = ['fields' => ['foo' => '']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame([], $requestParser->getSparseFieldset('foo'));
	}
	
	public function testHasSortFields() {
		$_GET = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasSortFields());
		
		$_GET = ['sort' => 'foo'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasSortFields());
	}
	
	public function testGetSortFields_Reformatted() {
		$_GET = ['sort' => 'foo'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame([['field' => 'foo', 'order' => RequestParser::SORT_ASCENDING]], $requestParser->getSortFields());
		
		$_GET = ['sort' => '-bar'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame([['field' => 'bar', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
		
		$_GET = ['sort' => 'foo,-bar'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame([['field' => 'foo', 'order' => RequestParser::SORT_ASCENDING], ['field' => 'bar', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
	}
	
	public function testGetSortFields_Raw() {
		$_GET = ['sort' => 'foo,-bar'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$options = ['useAnnotatedSortFields' => false];
		$this->assertSame(['foo', '-bar'], $requestParser->getSortFields($options));
	}
	
	public function testHasPagination() {
		$_GET = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasPagination());
		
		$_GET = ['page' => ['number' => '2', 'size' => '10']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasPagination());
	}
	
	public function testGetPagination() {
		$_GET = ['page' => ['number' => '2', 'size' => '10']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
	}
	
	public function testHasFilter() {
		$_GET = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasFilter());
		
		$_GET = ['filter' => 'foo'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasFilter());
	}
	
	public function testGetFilter() {
		$_GET = ['filter' => 'foo'];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame('foo', $requestParser->getFilter());
		
		$_GET = ['filter' => ['foo' => 'bar']];
		$_SERVER['REQUEST_URI'] = '/?'.http_build_query($_GET);
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame(['foo' => 'bar'], $requestParser->getFilter());
	}
	
	public function testHasAttribute() {
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasAttribute('foo'));
		$this->assertFalse($requestParser->hasAttribute('bar'));
		
		$_POST = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasAttribute('foo'));
		$this->assertFalse($requestParser->hasAttribute('bar'));
	}
	
	public function testGetAttribute() {
		$_POST = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame('bar', $requestParser->getAttribute('foo'));
	}
	
	public function testHasRelationship() {
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasRelationship('foo'));
		$this->assertFalse($requestParser->hasRelationship('bar'));
		
		$_POST = [
			'data' => [
				'relationships' => [
					'foo' => [
						'data' => [
							'type' => 'bar',
							'id'   => '42',
						],
					],
				],
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasRelationship('foo'));
		$this->assertFalse($requestParser->hasRelationship('bar'));
	}
	
	public function testGetRelationship() {
		$_POST = [
			'data' => [
				'relationships' => [
					'foo' => [
						'data' => [
							'type' => 'bar',
							'id'   => '42',
						],
					],
				],
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame(['data' => ['type' => 'bar', 'id' => '42']], $requestParser->getRelationship('foo'));
	}
	
	public function testHasMeta() {
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertFalse($requestParser->hasMeta('foo'));
		$this->assertFalse($requestParser->hasMeta('bar'));
		
		$_POST = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertTrue($requestParser->hasMeta('foo'));
		$this->assertFalse($requestParser->hasMeta('bar'));
	}
	
	public function testGetMeta() {
		$_POST = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame('bar', $requestParser->getMeta('foo'));
	}
	
	public function testGetDocument() {
		$_POST = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
				'relationships' => [
					'foo' => [
						'data' => [
							'type' => 'bar',
							'id'   => '42',
						],
					],
				],
			],
			'meta' => [
				'foo' => 'bar',
			],
			'foo' => 'bar',
		];
		
		$requestParser = RequestParser::fromSuperglobals();
		$this->assertSame($_POST, $requestParser->getDocument());
	}
}
