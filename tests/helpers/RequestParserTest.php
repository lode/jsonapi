<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\RequestParser;
use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceRequestInterface;
use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceServerRequestInterface;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase {
	public function testFromSuperglobals_HappyPath() {
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
		
		$_SERVER['REQUEST_SCHEME'] = 'https';
		$_SERVER['HTTP_HOST']      = 'example.org';
		$_SERVER['REQUEST_URI']    = '/user/42?'.http_build_query($_GET);
		$_SERVER['CONTENT_TYPE']   = Document::CONTENT_TYPE_OFFICIAL;
		
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
	
	public function testFromSuperglobals_WithPhpInputStream() {
		$_SERVER['REQUEST_SCHEME'] = 'https';
		$_SERVER['HTTP_HOST']      = 'example.org';
		$_SERVER['REQUEST_URI']    = '/';
		$_SERVER['CONTENT_TYPE']   = Document::CONTENT_TYPE_OFFICIAL;
		
		$_GET  = [];
		$_POST = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		
		$this->assertSame([], $requestParser->getDocument());
	}
	
	public function testFromSuperglobals_WithoutServerContext() {
		unset($_SERVER['REQUEST_SCHEME']);
		unset($_SERVER['HTTP_HOST']);
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['CONTENT_TYPE']);
		
		$_GET    = [];
		$_POST   = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		
		$this->assertSame([], $requestParser->getDocument());
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
	
	public function testFromPsrRequest_WithEmptyDocument() {
		$selfLink        = '';
		$queryParameters = [];
		$document        = null;
		
		$request       = new TestableNonInterfaceRequestInterface($selfLink, $queryParameters, $document);
		$requestParser = RequestParser::fromPsrRequest($request);
		
		$this->assertSame([], $requestParser->getDocument());
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
		$requestParser = new RequestParser('https://example.org/');
		$this->assertSame('https://example.org/', $requestParser->getSelfLink());
		
		$queryParameters = ['foo' => 'bar'];
		$selfLink        = 'https://example.org/user/42?'.http_build_query($queryParameters);
		
		$requestParser = new RequestParser($selfLink, $queryParameters);
		$this->assertSame($selfLink, $requestParser->getSelfLink());
	}
	
	public function testHasIncludePaths() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasIncludePaths());
		
		$queryParameters = ['include' => 'foo,bar,baz.baf'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertTrue($requestParser->hasIncludePaths());
	}
	
	public function testGetIncludePaths_Reformatted() {
		$paths = [
			'foo',
			'bar',
			'baz.baf',
			'baz.bat',
			'user.ship.wing',
			'user.ship.nose.window',
			'user.friends',
		];
		$expected = [
			'foo' => [],
			'bar' => [],
			'baz' => [
				'baf' => [],
				'bat' => [],
			],
			'user' => [
				'ship' => [
					'wing' => [],
					'nose' => [
						'window' => [],
					],
				],
				'friends' => [],
			],
		];
		
		$queryParameters = ['include' => implode(',', $paths)];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame($expected, $requestParser->getIncludePaths());
	}
	
	public function testGetIncludePaths_Raw() {
		$queryParameters = ['include' => 'foo,bar,baz.baf'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$options = ['useNestedIncludePaths' => false];
		$this->assertSame(['foo', 'bar', 'baz.baf'], $requestParser->getIncludePaths($options));
	}
	
	public function testHasSparseFieldset() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasSparseFieldset('foo'));
		
		$queryParameters = ['fields' => ['foo' => 'bar']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertTrue($requestParser->hasSparseFieldset('foo'));
	}
	
	public function testGetSparseFieldset() {
		$queryParameters = ['fields' => ['foo' => 'bar,baz']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame(['bar', 'baz'], $requestParser->getSparseFieldset('foo'));
		
		$queryParameters = ['fields' => ['foo' => '']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame([], $requestParser->getSparseFieldset('foo'));
	}
	
	public function testHasSortFields() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasSortFields());
		
		$queryParameters = ['sort' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertTrue($requestParser->hasSortFields());
	}
	
	public function testGetSortFields_Reformatted() {
		$queryParameters = ['sort' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame([['field' => 'foo', 'order' => RequestParser::SORT_ASCENDING]], $requestParser->getSortFields());
		
		$queryParameters = ['sort' => '-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame([['field' => 'bar', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
		
		$queryParameters = ['sort' => 'foo,-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame([['field' => 'foo', 'order' => RequestParser::SORT_ASCENDING], ['field' => 'bar', 'order' => RequestParser::SORT_DESCENDING]], $requestParser->getSortFields());
	}
	
	public function testGetSortFields_Raw() {
		$queryParameters = ['sort' => 'foo,-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$options = ['useAnnotatedSortFields' => false];
		$this->assertSame(['foo', '-bar'], $requestParser->getSortFields($options));
	}
	
	public function testHasPagination() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasPagination());
		
		$queryParameters = ['page' => ['number' => '2', 'size' => '10']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertTrue($requestParser->hasPagination());
	}
	
	public function testGetPagination() {
		$queryParameters = ['page' => ['number' => '2', 'size' => '10']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
	}
	
	public function testHasFilter() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasFilter());
		
		$queryParameters = ['filter' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertTrue($requestParser->hasFilter());
	}
	
	public function testGetFilter() {
		$queryParameters = ['filter' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame('foo', $requestParser->getFilter());
		
		$queryParameters = ['filter' => ['foo' => 'bar']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$this->assertSame(['foo' => 'bar'], $requestParser->getFilter());
	}
	
	public function testHasAttribute() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasAttribute('foo'));
		$this->assertFalse($requestParser->hasAttribute('bar'));
		
		$document = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertTrue($requestParser->hasAttribute('foo'));
		$this->assertFalse($requestParser->hasAttribute('bar'));
	}
	
	public function testGetAttribute() {
		$document = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertSame('bar', $requestParser->getAttribute('foo'));
	}
	
	public function testHasRelationship() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasRelationship('foo'));
		$this->assertFalse($requestParser->hasRelationship('bar'));
		
		$document = [
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
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertTrue($requestParser->hasRelationship('foo'));
		$this->assertFalse($requestParser->hasRelationship('bar'));
	}
	
	public function testGetRelationship() {
		$document = [
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
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertSame(['data' => ['type' => 'bar', 'id' => '42']], $requestParser->getRelationship('foo'));
	}
	
	public function testHasMeta() {
		$requestParser = new RequestParser();
		$this->assertFalse($requestParser->hasMeta('foo'));
		$this->assertFalse($requestParser->hasMeta('bar'));
		
		$document = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertTrue($requestParser->hasMeta('foo'));
		$this->assertFalse($requestParser->hasMeta('bar'));
	}
	
	public function testGetMeta() {
		$document = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertSame('bar', $requestParser->getMeta('foo'));
	}
	
	public function testGetDocument() {
		$document = [
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
		
		$requestParser = new RequestParser($selfLink='', $quaryParameters=[], $document);
		$this->assertSame($document, $requestParser->getDocument());
	}
}
