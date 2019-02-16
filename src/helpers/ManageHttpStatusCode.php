<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;

trait ManageHttpStatusCode {
	/** @var int */
	protected $httpStatusCode;
	
	/**
	 * spec api
	 */
	
	/**
	 * @param int $httpStatusCode
	 * 
	 * @throws InputException if an invalid code is used
	 */
	public function setHttpStatusCode($httpStatusCode) {
		if (Validator::checkHttpStatusCode($httpStatusCode) === false) {
			throw new InputException('can not use an invalid http status code');
		}
		
		$this->httpStatusCode = $httpStatusCode;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @return boolean
	 */
	public function hasHttpStatusCode() {
		return ($this->httpStatusCode !== null);
	}
	
	/**
	 * @internal
	 * 
	 * @return int
	 */
	public function getHttpStatusCode() {
		return $this->httpStatusCode;
	}
}
