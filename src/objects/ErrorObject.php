<?php

namespace alsvanzelf\jsonapi\objects;

class ErrorObject {
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
