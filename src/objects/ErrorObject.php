<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;

class ErrorObject implements ObjectInterface {
	public $status;
	public $code;
	
	/**
	 * human api
	 */
	
	public static function fromException(\Exception $exception) {
		$errorObject = new self();
		
		$errorObject->setErrorCode($exception->getMessage());
		if (self::isValidHttpStatusCode($exception->getCode())) {
			$errorObject->setHttpStatusCode($exception->getCode());
		}
		
		return $errorObject;
	}
	
	/**
	 * spec api
	 */
	
	public function setHttpStatusCode($httpStatusCode) {
		$this->status = (string) $httpStatusCode;
	}
	
	public function setErrorCode($errorCode) {
		$this->code = $errorCode;
	}
	
	/**
	 * output
	 */
	
	public function isEmpty() {
		if ($this->status !== null) {
			return false;
		}
		if ($this->code !== null) {
			return false;
		}
		
		return true;
	}
	
	public function toArray() {
		$array = [];
		
		if ($this->status !== null) {
			$array['status'] = $this->status;
		}
		if ($this->code !== null) {
			$array['code'] = $this->code;
		}
		
		return $array;
	}
	
	/**
	 * internal api
	 */
	
	private static function isValidHttpStatusCode($httpStatusCode) {
		$httpStatusCode = (int) $httpStatusCode;
		
		if ($httpStatusCode < 100) {
			return false;
		}
		if ($httpStatusCode >= 600) {
			return false;
		}
		
		return true;
	}
}
