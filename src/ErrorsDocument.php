<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\objects\ErrorObject;

class ErrorsDocument extends Document {
	/** @var ErrorObject[] */
	public $errors = [];
	/** @var array */
	private $httpStatusCodes;
	/** @var array */
	private static $defaults = [
		'exceptionExposeDetails' => false,
		'exceptionSkipPrevious'  => false,
	];
	
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
	 * @param  array      $options   optional {@see ErrorsDocument::$defaults}
	 * @return ErrorsDocument
	 */
	public static function fromException(\Exception $exception, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$errorsDocument = new self();
		$errorsDocument->addException($exception, $options);
		
		return $errorsDocument;
	}
	
	/**
	 * add an ErrorObject for the given $exception
	 * 
	 * recursively adds multiple ErrorObjects if $exception carries a ->getPrevious()
	 * 
	 * @param \Exception $exception
	 * @param array      $options   optional {@see ErrorsDocument::$defaults}
	 */
	public function addException(\Exception $exception, array $options=[]) {
		$options = array_merge(self::$defaults, $options);
		
		$this->addErrorObject(ErrorObject::fromException($exception, $options));
		
		if ($options['exceptionSkipPrevious'] === false) {
			$exception = $exception->getPrevious();
			while ($exception !== null) {
				$this->addException($exception, $options);
				$exception = $exception->getPrevious();
			}
		}
	}
	
	/**
	 * @param string|int $genericCode       developer-friendly code of the generic type of error
	 * @param string     $genericTitle      human-friendly title of the generic type of error
	 * @param string     $specificDetails   optional, human-friendly explanation of the specific error
	 * @param string     $specificAboutLink optional, human-friendly explanation of the specific error
	 */
	public function add($genericCode, $genericTitle, $specificDetails=null, $specificAboutLink=null) {
		$errorObject = new ErrorObject($genericCode, $genericTitle, $specificDetails, $specificAboutLink);
		
		$this->addErrorObject($errorObject);
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
			$this->setHttpStatusCode($this->determineHttpStatusCode($errorObject->status));
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
	
	/**
	 * internal api
	 */
	
	/**
	 * @param  string|int $httpStatusCode
	 * @return int
	 */
	private function determineHttpStatusCode($httpStatusCode) {
		// add the new code
		$category = substr($httpStatusCode, 0, 1);
		$this->httpStatusCodes[$category][$httpStatusCode] = true;
		
		$advisedStatusCode = $httpStatusCode;
		
		// when there's multiple, give preference to 5xx errors
		if (isset($this->httpStatusCodes['5']) && isset($this->httpStatusCodes['4'])) {
			// use a generic one
			$advisedStatusCode = 500;
		}
		elseif (isset($this->httpStatusCodes['5'])) {
			if (count($this->httpStatusCodes['5']) === 1) {
				$advisedStatusCode = key($this->httpStatusCodes['5']);
			}
			else {
				// use a generic one
				$advisedStatusCode = 500;
			}
		}
		elseif (isset($this->httpStatusCodes['4'])) {
			if (count($this->httpStatusCodes['4']) === 1) {
				$advisedStatusCode = key($this->httpStatusCodes['4']);
			}
			else {
				// use a generic one
				$advisedStatusCode = 400;
			}
		}
		
		return (int) $advisedStatusCode;
	}
}
