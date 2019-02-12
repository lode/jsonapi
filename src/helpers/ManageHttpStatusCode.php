<?php

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\InputException;

trait ManageHttpStatusCode {
	/** @var int */
	protected $httpStatusCode;
	
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
	 * @return boolean
	 */
	public function hasHttpStatusCode() {
		return ($this->httpStatusCode !== null);
	}
	
	/**
	 * @return int
	 */
	public function getHttpStatusCode() {
		return $this->httpStatusCode;
	}
}
