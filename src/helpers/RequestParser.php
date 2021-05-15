<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestParser {
	const SORT_ASCENDING  = 'ascending';
	const SORT_DESCENDING = 'descending';
	
	/** @var array */
	protected static $defaults = [
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
	/** @var string */
	private $selfLink = '';
	/** @var array */
	private $queryParameters = [];
	/** @var array */
	private $document = [];
	
	/**
	 * @param string $selfLink        the uri used to make this request {@see getSelfLink()}
	 * @param array  $queryParameters all query parameters defined by the specification
	 * @param array  $document        the request jsonapi document
	 */
	public function __construct($selfLink='', array $queryParameters=[], array $document=[]) {
		$this->selfLink        = $selfLink;
		$this->queryParameters = $queryParameters;
		$this->document        = $document;
	}
	
	/**
	 * @return self
	 * 
	 * @throws Exception if the body's json is invalid
	 */
	public static function fromSuperglobals() {
		$selfLink = '';
		if (isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
			$selfLink = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		
		$queryParameters = $_GET;
		
		$document = $_POST;
		if ($document === [] && isset($_SERVER['CONTENT_TYPE'])) {
			$documentIsJsonapi = (strpos($_SERVER['CONTENT_TYPE'], Document::CONTENT_TYPE_OFFICIAL) !== false);
			$documentIsJson    = (strpos($_SERVER['CONTENT_TYPE'], Document::CONTENT_TYPE_DEBUG)    !== false);
			
			if ($documentIsJsonapi || $documentIsJson) {
				$document = json_decode(file_get_contents('php://input'), true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					throw new Exception('error parsing request body: '.json_last_error_msg());
				}
				
				if ($document === null) {
					$document = [];
				}
			}
		}
		
		return new self($selfLink, $queryParameters, $document);
	}
	
	/**
	 * @param  ServerRequestInterface|RequestInterface $request
	 * @return self
	 * 
	 * @throws Exception if the body's json is invalid
	 */
	public static function fromPsrRequest(RequestInterface $request) {
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
			$document = json_decode($request->getBody()->getContents(), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new Exception('error parsing request body: '.json_last_error_msg());
			}
		}
		
		return new self($selfLink, $queryParameters, $document);
	}
	
	/**
	 * the full link used to make this request
	 * 
	 * this is not a bare self link of a resource and includes query parameters if used
	 * 
	 * @return string
	 */
	public function getSelfLink() {
		return $this->selfLink;
	}
	
	/**
	 * @return boolean
	 */
	public function hasIncludePaths() {
		return isset($this->queryParameters['include']);
	}
	
	/**
	 * returns a nested array based on the path, or the raw paths
	 * 
	 * the nested format allows easier processing on each step of the chain
	 * the raw format allows for custom processing
	 * 
	 * @param  array $options optional {@see RequestParser::$defaults}
	 * @return string[]|array
	 */
	public function getIncludePaths(array $options=[]) {
		$includePaths = explode(',', $this->queryParameters['include']);
		
		$options = array_merge(self::$defaults, $options);
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
	
	/**
	 * @param  string $type
	 * @return boolean
	 */
	public function hasSparseFieldset($type) {
		return isset($this->queryParameters['fields'][$type]);
	}
	
	/**
	 * @param  string $type
	 * @return string[]
	 */
	public function getSparseFieldset($type) {
		if ($this->queryParameters['fields'][$type] === '') {
			return [];
		}
		
		return explode(',', $this->queryParameters['fields'][$type]);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortFields() {
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
	 * @param  array $options optional {@see RequestParser::$defaults}
	 * @return string[]|array[] {
	 *         @var string $field the sort field, without any minus sign for descending sort order
	 *         @var string $order one of the RequestParser::SORT_* constants
	 * }
	 */
	public function getSortFields(array $options=[]) {
		$fields = explode(',', $this->queryParameters['sort']);
		
		$options = array_merge(self::$defaults, $options);
		if ($options['useAnnotatedSortFields'] === false) {
			return $fields;
		}
		
		$sort = [];
		foreach ($fields as $field) {
			$order = RequestParser::SORT_ASCENDING;
			
			if (strpos($field, '-') === 0) {
				$field = substr($field, 1);
				$order = RequestParser::SORT_DESCENDING;
			}
			
			$sort[] = [
				'field' => $field,
				'order' => $order,
			];
		}
		
		return $sort;
	}
	
	/**
	 * @return boolean
	 */
	public function hasPagination() {
		return isset($this->queryParameters['page']);
	}
	
	/**
	 * @todo return some kind of PaginatorObject which recognizes the strategy of pagination used
	 *       e.g. page-based, offset-based, cursor-based, or unknown
	 * 
	 * @return array
	 */
	public function getPagination() {
		return $this->queryParameters['page'];
	}
	
	/**
	 * @return boolean
	 */
	public function hasFilter() {
		return isset($this->queryParameters['filter']);
	}
	
	/**
	 * @return array
	 */
	public function getFilter() {
		return $this->queryParameters['filter'];
	}
	
	/**
	 * @param  string $attributeName
	 * @return boolean
	 */
	public function hasAttribute($attributeName) {
		if (isset($this->document['data']['attributes']) === false) {
			return false;
		}
		if (array_key_exists($attributeName, $this->document['data']['attributes']) === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param  string $attributeName
	 * @return mixed
	 */
	public function getAttribute($attributeName) {
		return $this->document['data']['attributes'][$attributeName];
	}
	
	/**
	 * @param  string $relationshipName
	 * @return boolean
	 */
	public function hasRelationship($relationshipName) {
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
	 * @param  string $relationshipName
	 * @return array
	 */
	public function getRelationship($relationshipName) {
		return $this->document['data']['relationships'][$relationshipName];
	}
	
	/**
	 * @param  string $metaKey
	 * @return boolean
	 */
	public function hasMeta($metaKey) {
		if (isset($this->document['meta']) === false) {
			return false;
		}
		if (array_key_exists($metaKey, $this->document['meta']) === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param  string $metaKey
	 * @return mixed
	 */
	public function getMeta($metaKey) {
		return $this->document['meta'][$metaKey];
	}
	
	/**
	 * @return array
	 */
	public function getDocument() {
		return $this->document;
	}
}
