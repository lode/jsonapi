<?php

namespace alsvanzelf\jsonapi;

abstract class Document {
	const JSONAPI_VERSION = '1.0';
	
	public $httpStatusCode = 200;
	
	/**
	 * options
	 */
	
	public function setHttpStatusCode($statusCode) {
		$this->httpStatusCode = $statusCode;
	}
	
	/**
	 * output
	 */
	
	public function toArray() {
		$array = [];
		
		$array['jsonapi'] = [
			'version' => Document::JSONAPI_VERSION,
		];
		
		return $array;
	}
	
	public function toJson($array=null) {
		$array = $array ?: $this->toArray();
		
		return json_encode($array, JSON_PRETTY_PRINT);
	}
	
	public function sendResponse($json=null) {
		$json = $json ?: $this->toJson();
		
		http_response_code($this->httpStatusCode);
		echo $json;
	}
}
