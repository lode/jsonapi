<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\enums\ContentTypeEnum;
use alsvanzelf\jsonapi\enums\SortOrderEnum;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @phpstan-consistent-constructor
 * warn when an extending constructor changes the arguments
 * that might break the class since we use `new static()`
 */
class RequestParser {
	/** @var PHPStanTypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * reformat the include query parameter paths to nested arrays
		 * this allows easier processing on each step of the chain
		 */
		'useNestedIncludePaths' => true,
		
		/**
		 * reformat the sort query parameter paths to separate the sort order
		 * this allows easier processing of sort orders and field names
		 */
		'useAnnotatedSortFields' => true,
	];
	
	/**
	 * @param string                                         $selfLink        the uri used to make this request {@see getSelfLink()}
	 * @param array<string, string|array<array-key, string>> $queryParameters all query parameters defined by the specification
	 * @param array<string, mixed>                           $document        the request jsonapi document
	 * 
	 * @throws \JsonException if $document's content type is json but it can't be json decoded
	 */
	public function __construct(
		private readonly string $selfLink='',
		private readonly array $queryParameters=[],
		private readonly array $document=[],
	) {}
	
	public static function fromSuperglobals(): static {
		$selfLink = '';
		if (isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
			$selfLink = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		
		$queryParameters = $_GET;
		
		$document = $_POST;
		if ($document === [] && isset($_SERVER['CONTENT_TYPE'])) {
			$documentIsJsonapi = (str_contains((string) $_SERVER['CONTENT_TYPE'], ContentTypeEnum::Official->value));
			$documentIsJson    = (str_contains((string) $_SERVER['CONTENT_TYPE'], ContentTypeEnum::Debug->value));
			
			$document = file_get_contents('php://input');
			if ($document === '') {
				$document = [];
			}
			elseif ($documentIsJsonapi || $documentIsJson) {
				$document = json_decode($document, true, flags: JSON_THROW_ON_ERROR);
			}
		}
		
		return new static($selfLink, $queryParameters, $document);
	}
	
	/**
	 * @throws \JsonException if the requests' document can't be json decoded
	 */
	public static function fromPsrRequest(ServerRequestInterface|RequestInterface $request): static {
		$selfLink = (string) $request->getUri();
		
		if ($request instanceof ServerRequestInterface) {
			$queryParameters = $request->getQueryParams();
		}
		else {
			$queryParameters = [];
			parse_str($request->getUri()->getQuery(), $queryParameters);
		}
		
		if ($request->getBody()->getContents() === '') {
			$document = [];
		}
		else {
			$document = json_decode($request->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
		}
		
		return new static($selfLink, $queryParameters, $document);
	}
	
	/**
	 * the full link used to make this request
	 * 
	 * this is not a bare self link of a resource and includes query parameters if used
	 */
	public function getSelfLink(): string {
		return $this->selfLink;
	}
	
	public function hasIncludePaths(): bool {
		return isset($this->queryParameters['include']);
	}
	
	/**
	 * returns a nested array based on the path, or the raw paths
	 * 
	 * the nested format allows easier processing on each step of the chain
	 * the raw format allows for custom processing
	 * 
	 * @param  PHPStanTypeAlias_InternalOptions $options {@see RequestParser::$defaults}
	 * @return string[]|array
	 */
	public function getIncludePaths(array $options=[]): array {
		if ($this->queryParameters['include'] === '') {
			return [];
		}
		
		$includePaths = explode(',', (string) $this->queryParameters['include']);
		
		$options = [...self::$defaults, ...$options];
		if ($options['useNestedIncludePaths'] === false) {
			return $includePaths;
		}
		
		$restructured = [];
		foreach ($includePaths as $path) {
			$steps = explode('.', $path);
			
			$wrapped = [];
			while ($steps !== []) {
				$lastStep = array_pop($steps);
				$wrapped  = [$lastStep => $wrapped];
			}
			
			$restructured = array_merge_recursive($restructured, $wrapped);
		}
		
		return $restructured;
	}
	
	public function hasSparseFieldset(string $type): bool {
		return isset($this->queryParameters['fields'][$type]);
	}
	
	/**
	 * @return string[]
	 */
	public function getSparseFieldset(string $type): array {
		if ($this->queryParameters['fields'][$type] === '') {
			return [];
		}
		
		return explode(',', (string) $this->queryParameters['fields'][$type]);
	}
	
	public function hasSortFields(): bool {
		return isset($this->queryParameters['sort']);
	}
	
	/**
	 * returns an array with sort order annotations, or the raw sort fields with minus signs
	 * 
	 * the annotated format allows easier processing of sort orders and field names
	 * the raw format allows for custom processing
	 * 
	 * @todo return some kind of SortFieldObject
	 * 
	 * @param  PHPStanTypeAlias_InternalOptions $options {@see RequestParser::$defaults}
	 * @return string[]|array<array{
	 *         field: string, // the sort field, without any minus sign for descending sort order
	 *         order: SortOrderEnum,
	 * }>
	 */
	public function getSortFields(array $options=[]): array {
		if ($this->queryParameters['sort'] === '') {
			return [];
		}
		
		$fields = explode(',', (string) $this->queryParameters['sort']);
		
		$options = [...self::$defaults, ...$options];
		if ($options['useAnnotatedSortFields'] === false) {
			return $fields;
		}
		
		$sort = [];
		foreach ($fields as $field) {
			$order = SortOrderEnum::Ascending;
			
			if (str_starts_with($field, '-')) {
				$field = substr($field, 1);
				$order = SortOrderEnum::Descending;
			}
			
			$sort[] = [
				'field' => $field,
				'order' => $order,
			];
		}
		
		return $sort;
	}
	
	public function hasPagination(): bool {
		return isset($this->queryParameters['page']);
	}
	
	/**
	 * @todo return some kind of PaginatorObject which recognizes the strategy of pagination used
	 *       e.g. page-based, offset-based, cursor-based, or unknown
	 * 
	 * @return array<string, string>
	 */
	public function getPagination(): array {
		return $this->queryParameters['page'];
	}
	
	public function hasFilter(): bool {
		return isset($this->queryParameters['filter']);
	}
	
	/**
	 * @return string|array<array-key, string>
	 */
	public function getFilter(): string|array {
		return $this->queryParameters['filter'];
	}
	
	public function hasLocalId(): bool {
		return (isset($this->document['data']['lid']));
	}
	
	public function getLocalId(): string {
		return $this->document['data']['lid'];
	}
	
	public function hasAttribute(string $attributeName): bool {
		if (isset($this->document['data']['attributes']) === false) {
			return false;
		}
		if (array_key_exists($attributeName, $this->document['data']['attributes']) === false) {
			return false;
		}
		
		return true;
	}
	
	public function getAttribute(string $attributeName): mixed {
		return $this->document['data']['attributes'][$attributeName];
	}
	
	public function hasRelationship(string $relationshipName): bool {
		if (isset($this->document['data']['relationships']) === false) {
			return false;
		}
		if (array_key_exists($relationshipName, $this->document['data']['relationships']) === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @todo return some kind of read-only ResourceIdentifierObject
	 * 
	 * @return ?array<string, mixed>
	 */
	public function getRelationship(string $relationshipName): ?array {
		return $this->document['data']['relationships'][$relationshipName];
	}
	
	public function hasMeta(string $metaKey): bool {
		if (isset($this->document['meta']) === false) {
			return false;
		}
		if (array_key_exists($metaKey, $this->document['meta']) === false) {
			return false;
		}
		
		return true;
	}
	
	public function getMeta(string $metaKey): mixed {
		return $this->document['meta'][$metaKey];
	}
	
	/**
	 * @return array<string, mixed>
	 */
	public function getDocument(): array {
		return $this->document;
	}
}
