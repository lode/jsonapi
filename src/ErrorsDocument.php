<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\objects\ErrorObject;

class ErrorsDocument extends Document {
	/** @var ErrorObject[] */
	public $errors = [];
	
	/**
	 * @param ErrorObject $errorObject optional
	 */
	public function __construct(ErrorObject $errorObject=null) {
		parent::__construct();
		
		if ($errorObject !== null) {
			$this->addErrorObject($errorObject);
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param  \Exception $exception
	 * @return ErrorsObject
	 */
	public static function fromException(\Exception $exception) {
		return new self(ErrorObject::fromException($exception));
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @note also defines the http status code of the document if the ErrorObject has it defined
	 * 
	 * @param ErrorObject $errorObject
	 */
	public function addErrorObject(ErrorObject $errorObject) {
		$this->errors[] = $errorObject;
		
		if ($errorObject->status !== null) {
			$this->setHttpStatusCode($errorObject->status);
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		$array['errors'] = [];
		foreach ($this->errors as $error) {
			if ($error->isEmpty()) {
				continue;
			}
			
			$array['errors'][] = $error->toArray();
		}
		
		return $array;
	}
}
