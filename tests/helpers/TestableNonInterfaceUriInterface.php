<?php

namespace alsvanzelf\jsonapiTests\helpers;

use Psr\Http\Message\UriInterface;

class TestableNonInterfaceUriInterface implements UriInterface {
	protected $selfLink;
	protected $queryParameters;
	
	public function __construct($selfLink, $queryParameters) {
		$this->selfLink        = $selfLink;
		$this->queryParameters = $queryParameters;
	}
	
	/**
	 * UriInterface
	 */
	
	public function getQuery() {
		return http_build_query($this->queryParameters);
	}
	
	public function __toString() {
		return $this->selfLink;
	}
	
	// not used in current implementation
	public function getScheme() {
		return '';
	}
	
	public function getAuthority() {
		return '';
	}
	
	public function getUserInfo() {
		return '';
	}
	
	public function getHost() {
		return '';
	}
	
	public function getPort() {
		return null;
	}
	
	public function getPath() {
		return '';
	}
	
	public function getFragment() {
		return '';
	}
	
	public function withScheme($scheme) {
		return $this;
	}
	
	public function withUserInfo($user, $password = null) {
		return $this;
	}
	
	public function withHost($host) {
		return $this;
	}
	
	public function withPort($port) {
		return $this;
	}
	
	public function withPath($path) {
		return $this;
	}
	
	public function withQuery($query) {
		return $this;
	}
	
	public function withFragment($fragment) {
		return $this;
	}
}
