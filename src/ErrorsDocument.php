<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\objects\ErrorObject;

/**
 * this document is used to send one or multiple errors
 */
class ErrorsDocument extends Document {
	/** @var ErrorObject[] */
	protected $errors = [];
	/** @var array */
	protected $httpStatusCodes;
	/** @var array */
	protected static $defaults = [
		/**
		 * add the trace of exceptions when adding exceptions
		 * in some cases it might be handy to disable if traces are too big
		 */
		'includeExceptionTrace' => true,
		
		/**
		 * add previous exceptions as separate errors when adding exceptions
		 */
		'includeExceptionPrevious' => true,
	];
	
	/**
	 * @param ?ErrorObject $errorObject optional
	 */
	public function __construct(?ErrorObject $errorObject=null) {
		parent::__construct();
		
		if ($errorObject !== null) {
			$this->addErrorObject($errorObject);
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param  \Exception|\Throwable $exception
	 * @param  array                 $options   optional {@see ErrorsDocument::$defaults}
	 * @return ErrorsDocument
	 * 
	 * @throws InputException if $exception is not \Exception or \Throwable
	 */
	public static function fromException($exception, array $options=[]) {
		if ($exception instanceof \Exception === false && $exception instanceof \Throwable === false) {
			throw new InputException('input is not a real exception in php5 or php7');
		}
		
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
	 * @param \Exception|\Throwable $exception
	 * @param array                 $options   optional {@see ErrorsDocument::$defaults}
	 * 
	 * @throws InputException if $exception is not \Exception or \Throwable
	 */
	public function addException($exception, array $options=[]) {
		if ($exception instanceof \Exception === false && $exception instanceof \Throwable === false) {
			throw new InputException('input is not a real exception in php5 or php7');
		}
		
		$options = array_merge(self::$defaults, $options);
		
		$this->addErrorObject(ErrorObject::fromException($exception, $options));
		
		if ($options['includeExceptionPrevious']) {
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
	 * @param string     $genericTypeLink   optional, human-friendly explanation of the generic type of error
	 */
	public function add($genericCode, $genericTitle, $specificDetails=null, $specificAboutLink=null, $genericTypeLink=null) {
		$errorObject = new ErrorObject($genericCode, $genericTitle, $specificDetails, $specificAboutLink, $genericTypeLink);
		
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
		
		if ($errorObject->hasHttpStatusCode()) {
			$this->setHttpStatusCode($this->determineHttpStatusCode($errorObject->getHttpStatusCode()));
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
	 * @internal
	 * 
	 * @param  string|int $httpStatusCode
	 * @return int
	 */
	protected function determineHttpStatusCode($httpStatusCode) {
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
