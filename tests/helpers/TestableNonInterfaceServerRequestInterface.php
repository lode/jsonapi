<?php

namespace alsvanzelf\jsonapiTests\helpers;

use alsvanzelf\jsonapiTests\helpers\TestableNonInterfaceRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestableNonInterfaceServerRequestInterface extends TestableNonInterfaceRequestInterface implements ServerRequestInterface {
	/**
	 * ServerRequestInterface
	 */
	
	public function getQueryParams() {
		return $this->queryParameters;
	}
	
	// not used in current implementation
	public function getServerParams() {}
	public function getCookieParams() {}
	public function withCookieParams(array $cookies) {}
	public function withQueryParams(array $query) {}
	public function getUploadedFiles() {}
	public function withUploadedFiles(array $uploadedFiles) {}
	public function getParsedBody() {}
	public function withParsedBody($data) {}
	public function getAttributes() {}
	public function getAttribute($name, $default = null) {}
	public function withAttribute($name, $value) {}
	public function withoutAttribute($name) {}
}
