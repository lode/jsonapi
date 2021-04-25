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
	public function getRequestTarget() {}
	public function withRequestTarget($requestTarget) {}
	public function getMethod() {}
	public function withMethod($method) {}
	public function withUri(UriInterface $uri, $preserveHost = false) {}
	
	/**
	 * MessageInterface
	 */
	
	public function getBody() {
		return new TestableNonInterfaceStreamInterface($this->document);
	}
	
	// not used in current implementation
	public function getProtocolVersion() {}
	public function withProtocolVersion($version) {}
	public function getHeaders() {}
	public function hasHeader($name) {}
	public function getHeader($name) {}
	public function getHeaderLine($name) {}
	public function withHeader($name, $value) {}
	public function withAddedHeader($name, $value) {}
	public function withoutHeader($name) {}
	public function withBody(StreamInterface $body) {}
}
