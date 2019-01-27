<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinksObject;

class ErrorObject implements ObjectInterface {
	/** @var string */
	public $status;
	/** @var string */
	public $code;
	/** @var LinksObject */
	public $links;
	
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
	 * @param string $key
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function addLink($key, $href, array $meta=[]) {
		if ($this->links === null) {
			$this->setLinksObject(new LinksObject());
		}
		
		$this->links->add($key, $href, $meta);
	}
	
	/**
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setAboutLink($href, array $meta=[]) {
		$this->addLink('about', $href, $meta);
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
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
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
		if ($this->links !== null && $this->links->isEmpty() === false) {
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
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
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
