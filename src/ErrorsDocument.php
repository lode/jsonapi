<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\objects\ErrorObject;

class ErrorsDocument extends Document {
	public $errors = [];
	
	public function __construct(ErrorObject $errorObject=null) {
		if ($errorObject !== null) {
			$this->addErrorObject($errorObject);
		}
	}
	
	/**
	 * human api
	 */
	
	public static function fromException(\Exception $exception) {
		return new self(ErrorObject::fromException($exception));
	}
	
	/**
	 * spec api
	 */
	
	public function addErrorObject(ErrorObject $errorObject) {
		$this->errors[] = $errorObject;
		
		if ($errorObject->status !== null) {
			$this->setHttpStatusCode($errorObject->status);
		}
	}
	
	/**
	 * output
	 */
	
	public function toArray() {
		$array = parent::toArray();
		
		$array['errors'] = [];
		foreach ($this->errors as $error) {
			$array['errors'] = $error;
		}
		
		return $array;
	}
}
