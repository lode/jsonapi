<?php

namespace alsvanzelf\jsonapi\helpers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestParser {
	const SORT_ASCENDING  = 'ascending';
	const SORT_DESCENDING = 'descending';
	
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
	 */
	public static function fromSuperglobals() {
		$selfLink = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		$queryParameters = $_GET;
		
		$document = $_POST;
		if ($document === [] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
			$document = json_decode(file_get_contents('php://input'), true);
		}
		
		return new self($selfLink, $queryParameters, $document);
	}
	
	/**
	 * @param  ServerRequestInterface|RequestInterface $request
	 * @return self
	 */
	public static function fromPsrRequest(RequestInterface $request) {
		$selfLink = (string) $request->getUri();
		
		if ($request instanceof ServerRequestInterface) {
			$queryParameters = $request->getQueryParams();
		}
		else {
			$queryParameters = '';
			parse_str($request->getUri()->getQuery(), $queryParameters);
		}
		
		$document = json_decode($request->getBody()->getContents(), true);
		
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
	 * @todo optionally, return a nested array based on the path
	 * 
	 * @return string[]
	 */
	public function getIncludePaths() {
		return explode(',', $this->queryParameters['include']);
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
		return explode(',', $this->queryParameters['fields'][$type]);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortFields() {
		return isset($this->queryParameters['sort']);
	}
	
	/**
	 * @todo return some kind of SortFieldObject
	 * 
	 * @return array {
	 *         @var string $field the sort field, without any minus sign for descending sort order
	 *         @var string $order one of the RequestParser::SORT_* constants
	 * }
	 */
	public function getSortFields() {
		$fields = explode(',', $this->queryParameters['sort']);
		
		$sort = [];
		foreach ($fields as $field) {
			$order = RequestParser::SORT_ASCENDING;
			
			if (strpos($name, '-') === 0) {
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
	 * @param  string $fieldName
	 * @return boolean
	 */
	public function hasAttribute($fieldName) {
		return array_key_exists($fieldName, $this->document['data']['attributes']);
	}
	
	/**
	 * @param  string $fieldName
	 * @return mixed
	 */
	public function getAttribute($fieldName) {
		return $this->document['data']['attributes'][$fieldName];
	}
	
	/**
	 * @param  string $fieldName
	 * @return boolean
	 */
	public function hasRelationship($fieldName) {
		return array_key_exists($fieldName, $this->document['data']['relationships']);
	}
	
	/**
	 * @todo return some kind of read-only ResourceIdentifierObject
	 * 
	 * @param  string $fieldName
	 * @return array
	 */
	public function getRelationship($fieldName) {
		return $this->document['data']['relationships'][$fieldName];
	}
	
	/**
	 * @param  string $metaKey
	 * @return boolean
	 */
	public function hasMeta($metaKey) {
		return array_key_exists($metaKey, $this->document['meta']);
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
