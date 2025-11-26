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
	public function getServerParams() {
		return [];
	}
	
	public function getCookieParams() {
		return [];
	}
	
	public function withCookieParams(array $cookies) {
		return $this;
	}
	
	public function withQueryParams(array $query) {
		return $this;
	}
	
	public function getUploadedFiles() {
		return [];
	}
	
	public function withUploadedFiles(array $uploadedFiles) {
		return $this;
	}
	
	public function getParsedBody() {}
	public function withParsedBody($data) {
		return $this;
	}
	
	public function getAttributes() {
		return [];
	}
	
	public function getAttribute($name, $default = null) {
		return null;
	}
	
	public function withAttribute($name, $value) {
		return $this;
	}
	
	public function withoutAttribute($name) {
		return $this;
	}
}
