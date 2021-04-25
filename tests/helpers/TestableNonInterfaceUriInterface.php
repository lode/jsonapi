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
	public function getScheme() {}
	public function getAuthority() {}
	public function getUserInfo() {}
	public function getHost() {}
	public function getPort() {}
	public function getPath() {}
	public function getFragment() {}
	public function withScheme($scheme) {}
	public function withUserInfo($user, $password = null) {}
	public function withHost($host) {}
	public function withPort($port) {}
	public function withPath($path) {}
	public function withQuery($query) {}
	public function withFragment($fragment) {}
}
