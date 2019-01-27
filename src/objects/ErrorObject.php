<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;

class ErrorObject implements ObjectInterface {
	/** @var string */
	public $status;
	/** @var string */
	public $code;
	
	/**
	 * human api
	 */
	
	/**
	 * @param  \Exception $exception
	 * @return ErrorObject
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
	
	/**
	 * the HTTP status code applicable to this problem
	 * 
	 * @param string|int $httpStatusCode will be casted to a string
	 */
	public function setHttpStatusCode($httpStatusCode) {
		$this->status = (string) $httpStatusCode;
	}
	
	/**
	 * an application-specific error code, expressed as a string value
	 * 
	 * @param string $errorCode
	 */
	public function setErrorCode($errorCode) {
		$this->code = $errorCode;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
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
	
	/**
	 * @inheritDoc
	 */
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
	
	/**
	 * @param  string|int $httpStatusCode
	 * @return boolean
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
