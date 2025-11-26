<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceUriInterface;
use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceStreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class TestableNonInterfaceRequestInterface implements RequestInterface {
	protected $selfLink;
	protected $queryParameters;
	protected $document;
	
	public function __construct($selfLink, $queryParameters, $document) {
		$this->selfLink        = $selfLink;
		$this->queryParameters = $queryParameters;
		$this->document        = $document;
	}
	
	/**
	 * RequestInterface
	 */
	
	public function getUri() {
		return new TestableNonInterfaceUriInterface($this->selfLink, $this->queryParameters);
	}
	
	// not used in current implementation
	public function getRequestTarget() {
		return '';
	}
	
	public function withRequestTarget($requestTarget) {
		return $this;
	}
	
	public function getMethod() {
		return '';
	}
	
	public function withMethod($method) {
		return $this;
	}
	
	public function withUri(UriInterface $uri, $preserveHost = false) {
		return $this;
	}
	
	
	/**
	 * MessageInterface
	 */
	
	public function getBody() {
		return new TestableNonInterfaceStreamInterface($this->document);
	}
	
	// not used in current implementation
	public function getProtocolVersion() {
		return '';
	}
	
	public function withProtocolVersion($version) {
		return $this;
	}
	
	public function getHeaders() {
		return [['']];
	}
	
	public function hasHeader($name) {
		return false;
	}
	
	public function getHeader($name) {
		return [''];
	}
	
	public function getHeaderLine($name) {
		return '';
	}
	
	public function withHeader($name, $value) {
		return $this;
	}
	
	public function withAddedHeader($name, $value) {
		return $this;
	}
	
	public function withoutHeader($name) {
		return $this;
	}
	
	public function withBody(StreamInterface $body) {
		return $this;
	}
}
