<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\enums\SortOrderEnum;
use alsvanzelf\jsonapi\helpers\RequestParser;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestParserTest extends TestCase {
	public function testFromSuperglobals_HappyPath(): void {
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
		$_SERVER['CONTENT_TYPE']   = ContentTypeEnum::Official->value;
		
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
		
		parent::assertSame('https://example.org/user/42?'.http_build_query($_GET), $requestParser->getSelfLink());
		
		parent::assertTrue($requestParser->hasIncludePaths());
		parent::assertTrue($requestParser->hasSparseFieldset('user'));
		parent::assertTrue($requestParser->hasSortFields());
		parent::assertTrue($requestParser->hasPagination());
		parent::assertTrue($requestParser->hasFilter());
		
		parent::assertSame(['ship' => ['wing' => []]], $requestParser->getIncludePaths());
		parent::assertSame(['name', 'location'], $requestParser->getSparseFieldset('user'));
		parent::assertSame([['field' => 'name', 'order' => SortOrderEnum::Ascending], ['field' => 'location', 'order' => SortOrderEnum::Descending]], $requestParser->getSortFields());
		parent::assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
		parent::assertSame('42', $requestParser->getFilter());
		
		parent::assertTrue($requestParser->hasAttribute('name'));
		parent::assertTrue($requestParser->hasRelationship('ship'));
		parent::assertTrue($requestParser->hasMeta('lock'));
		
		parent::assertSame('Foo', $requestParser->getAttribute('name'));
		parent::assertSame(['data' => ['type' => 'ship', 'id' => '42']], $requestParser->getRelationship('ship'));
		parent::assertTrue($requestParser->getMeta('lock'));
		
		parent::assertSame($_POST, $requestParser->getDocument());
	}
	
	public function testFromSuperglobals_WithPhpInputStream(): void {
		$_SERVER['REQUEST_SCHEME'] = 'https';
		$_SERVER['HTTP_HOST']      = 'example.org';
		$_SERVER['REQUEST_URI']    = '/';
		$_SERVER['CONTENT_TYPE']   = ContentTypeEnum::Official->value;
		
		$_GET  = [];
		$_POST = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		
		parent::assertSame([], $requestParser->getDocument());
	}
	
	public function testFromSuperglobals_WithoutServerContext(): void {
		unset($_SERVER['REQUEST_SCHEME']);
		unset($_SERVER['HTTP_HOST']);
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['CONTENT_TYPE']);
		
		$_GET    = [];
		$_POST   = [];
		
		$requestParser = RequestParser::fromSuperglobals();
		
		parent::assertSame([], $requestParser->getDocument());
	}
	
	public function testFromPsrRequest_WithRequestInterface(): void {
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
		
		$request = parent::createConfiguredStub(RequestInterface::class, [
			'getBody'        => parent::createConfiguredStub(StreamInterface::class, ['getContents' => json_encode($document)]),
			'getUri'         => parent::createConfiguredStub(UriInterface::class, [
				'__toString' => $selfLink,
				'getQuery'   => http_build_query($queryParameters),
			]),
		]);
		
		$requestParser = RequestParser::fromPsrRequest($request);
		
		parent::assertSame($selfLink, $requestParser->getSelfLink());
		
		parent::assertTrue($requestParser->hasIncludePaths());
		parent::assertTrue($requestParser->hasSparseFieldset('user'));
		parent::assertTrue($requestParser->hasSortFields());
		parent::assertTrue($requestParser->hasPagination());
		parent::assertTrue($requestParser->hasFilter());
		
		parent::assertSame(['ship' => ['wing' => []]], $requestParser->getIncludePaths());
		parent::assertSame(['name', 'location'], $requestParser->getSparseFieldset('user'));
		parent::assertSame([['field' => 'name', 'order' => SortOrderEnum::Ascending], ['field' => 'location', 'order' => SortOrderEnum::Descending]], $requestParser->getSortFields());
		parent::assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
		parent::assertSame('42', $requestParser->getFilter());
		
		parent::assertTrue($requestParser->hasAttribute('name'));
		parent::assertTrue($requestParser->hasRelationship('ship'));
		parent::assertTrue($requestParser->hasMeta('lock'));
		
		parent::assertSame('Foo', $requestParser->getAttribute('name'));
		parent::assertSame(['data' => ['type' => 'ship', 'id' => '42']], $requestParser->getRelationship('ship'));
		parent::assertTrue($requestParser->getMeta('lock'));
		
		parent::assertSame($document, $requestParser->getDocument());
	}
	
	public function testFromPsrRequest_WithEmptyDocument(): void {
		$selfLink        = '';
		$queryParameters = [];
		$document        = null;
		
		$request = parent::createConfiguredStub(RequestInterface::class, [
			'getBody' => parent::createConfiguredStub(StreamInterface::class, ['getContents' => '']),
			'getUri'  => parent::createConfiguredStub(UriInterface::class, ['getQuery'   => '']),
		]);
		
		$requestParser = RequestParser::fromPsrRequest($request);
		
		parent::assertSame([], $requestParser->getDocument());
	}
	
	public function testFromPsrRequest_WithServerRequestInterface(): void {
		$queryParameters = [
			'sort' => 'name,-location',
		];
		$selfLink = 'https://example.org/user/42?'.http_build_query($queryParameters);
		
		$request = parent::createConfiguredStub(ServerRequestInterface::class, [
			'getBody'        => parent::createConfiguredStub(StreamInterface::class, ['getContents' => '']),
			'getQueryParams' => $queryParameters,
			'getUri'         => parent::createConfiguredStub(UriInterface::class, ['__toString' => $selfLink]),
		]);
		$requestParser = RequestParser::fromPsrRequest($request);
		
		parent::assertSame($selfLink, $requestParser->getSelfLink());
		parent::assertTrue($requestParser->hasSortFields());
		parent::assertSame([['field' => 'name', 'order' => SortOrderEnum::Ascending], ['field' => 'location', 'order' => SortOrderEnum::Descending]], $requestParser->getSortFields());
	}
	
	public function testGetSelfLink(): void {
		$requestParser = new RequestParser('https://example.org/');
		parent::assertSame('https://example.org/', $requestParser->getSelfLink());
		
		$queryParameters = ['foo' => 'bar'];
		$selfLink        = 'https://example.org/user/42?'.http_build_query($queryParameters);
		
		$requestParser = new RequestParser($selfLink, $queryParameters);
		parent::assertSame($selfLink, $requestParser->getSelfLink());
	}
	
	public function testHasIncludePaths(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasIncludePaths());
		
		$queryParameters = ['include' => 'foo,bar,baz.baf'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertTrue($requestParser->hasIncludePaths());
	}
	
	public function testGetIncludePaths_Reformatted(): void {
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
		parent::assertSame($expected, $requestParser->getIncludePaths());
	}
	
	public function testGetIncludePaths_Raw(): void {
		$queryParameters = ['include' => 'foo,bar,baz.baf'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$options = ['useNestedIncludePaths' => false];
		parent::assertSame(['foo', 'bar', 'baz.baf'], $requestParser->getIncludePaths($options));
	}
	
	public function testGetIncludePaths_Empty(): void {
		$queryParameters = ['include' => ''];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		
		parent::assertTrue($requestParser->hasIncludePaths());
		parent::assertSame([], $requestParser->getIncludePaths());
	}
	
	public function testHasSparseFieldset(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasSparseFieldset('foo'));
		
		$queryParameters = ['fields' => ['foo' => 'bar']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertTrue($requestParser->hasSparseFieldset('foo'));
	}
	
	public function testGetSparseFieldset(): void {
		$queryParameters = ['fields' => ['foo' => 'bar,baz']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame(['bar', 'baz'], $requestParser->getSparseFieldset('foo'));
		
		$queryParameters = ['fields' => ['foo' => '']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame([], $requestParser->getSparseFieldset('foo'));
	}
	
	public function testHasSortFields(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasSortFields());
		
		$queryParameters = ['sort' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertTrue($requestParser->hasSortFields());
	}
	
	public function testGetSortFields_Reformatted(): void {
		$queryParameters = ['sort' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame([['field' => 'foo', 'order' => SortOrderEnum::Ascending]], $requestParser->getSortFields());
		
		$queryParameters = ['sort' => '-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame([['field' => 'bar', 'order' => SortOrderEnum::Descending]], $requestParser->getSortFields());
		
		$queryParameters = ['sort' => 'foo,-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame([['field' => 'foo', 'order' => SortOrderEnum::Ascending], ['field' => 'bar', 'order' => SortOrderEnum::Descending]], $requestParser->getSortFields());
	}
	
	public function testGetSortFields_Raw(): void {
		$queryParameters = ['sort' => 'foo,-bar'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		$options = ['useAnnotatedSortFields' => false];
		parent::assertSame(['foo', '-bar'], $requestParser->getSortFields($options));
	}
	
	public function testGetSortFields_Empty(): void {
		$queryParameters = ['sort' => ''];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		
		parent::assertTrue($requestParser->hasSortFields());
		parent::assertSame([], $requestParser->getSortFields());
	}
	
	public function testHasPagination(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasPagination());
		
		$queryParameters = ['page' => ['number' => '2', 'size' => '10']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertTrue($requestParser->hasPagination());
	}
	
	public function testGetPagination(): void {
		$queryParameters = ['page' => ['number' => '2', 'size' => '10']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame(['number' => '2', 'size' => '10'], $requestParser->getPagination());
	}
	
	public function testHasFilter(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasFilter());
		
		$queryParameters = ['filter' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertTrue($requestParser->hasFilter());
	}
	
	public function testGetFilter(): void {
		$queryParameters = ['filter' => 'foo'];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame('foo', $requestParser->getFilter());
		
		$queryParameters = ['filter' => ['foo' => 'bar']];
		$requestParser = new RequestParser($selfLink='', $queryParameters);
		parent::assertSame(['foo' => 'bar'], $requestParser->getFilter());
	}
	
	public function testHasLocalId(): void {
		$document = [
			'data' => [
				'id' => 'foo',
			],
		];
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		
		parent::assertArrayHasKey('data', $requestParser->getDocument());
		parent::assertArrayHasKey('id', $requestParser->getDocument()['data']);
		parent::assertFalse($requestParser->hasLocalId());
		
		$document = [
			'data' => [
				'lid' => 'foo',
			],
		];
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		
		parent::assertArrayHasKey('data', $requestParser->getDocument());
		parent::assertArrayNotHasKey('id', $requestParser->getDocument()['data']);
		parent::assertTrue($requestParser->hasLocalId());
		parent::assertSame('foo', $requestParser->getLocalId());
	}
	
	public function testHasAttribute(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasAttribute('foo'));
		parent::assertFalse($requestParser->hasAttribute('bar'));
		
		$document = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertTrue($requestParser->hasAttribute('foo'));
		parent::assertFalse($requestParser->hasAttribute('bar'));
	}
	
	public function testGetAttribute(): void {
		$document = [
			'data' => [
				'attributes' => [
					'foo' => 'bar',
				],
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertSame('bar', $requestParser->getAttribute('foo'));
	}
	
	public function testHasRelationship(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasRelationship('foo'));
		parent::assertFalse($requestParser->hasRelationship('bar'));
		
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
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertTrue($requestParser->hasRelationship('foo'));
		parent::assertFalse($requestParser->hasRelationship('bar'));
	}
	
	public function testGetRelationship(): void {
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
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertSame(['data' => ['type' => 'bar', 'id' => '42']], $requestParser->getRelationship('foo'));
	}
	
	public function testHasMeta(): void {
		$requestParser = new RequestParser();
		parent::assertFalse($requestParser->hasMeta('foo'));
		parent::assertFalse($requestParser->hasMeta('bar'));
		
		$document = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertTrue($requestParser->hasMeta('foo'));
		parent::assertFalse($requestParser->hasMeta('bar'));
	}
	
	public function testGetMeta(): void {
		$document = [
			'meta' => [
				'foo' => 'bar',
			],
		];
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertSame('bar', $requestParser->getMeta('foo'));
	}
	
	public function testGetDocument(): void {
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
		
		$requestParser = new RequestParser($selfLink='', $queryParameters=[], $document);
		parent::assertSame($document, $requestParser->getDocument());
	}
}
