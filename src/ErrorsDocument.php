<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\objects\ErrorObject;

/**
 * this document is used to send one or multiple errors
 */
class ErrorsDocument extends Document {
	/** @var ErrorObject[] */
	protected array $errors = [];
	/** @var array<string, array<int, true>> */
	protected array $httpStatusCodes = [];
	/** @var PHPStanTypeAlias_DefaultOptions_ErrorsDocument */
	protected static array $errorsDocumentDefaults = [
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
	 * @param PHPStanTypeAlias_Options_ErrorsDocument $options {@see ErrorsDocument::$errorsDocumentDefaults}
	 */
	public static function fromException(\Throwable $exception, array $options=[]): static {
		$options = [...self::$errorsDocumentDefaults, ...$options];
		
		$errorsDocument = new static();
		$errorsDocument->addException($exception, $options);
		
		return $errorsDocument;
	}
	
	/**
	 * add an ErrorObject for the given $exception
	 * 
	 * recursively adds multiple ErrorObjects if $exception carries a ->getPrevious()
	 * 
	 * @param PHPStanTypeAlias_Options_ErrorsDocumentAndErrorObject $options {@see ErrorsDocument::$errorsDocumentDefaults}
	 */
	public function addException(\Throwable $exception, array $options=[]): void {
		$options = [...self::$errorsDocumentDefaults, ...$options];
		
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
	 * @param string     $specificDetails   human-friendly explanation of the specific error
	 * @param string     $specificAboutLink human-friendly explanation of the specific error
	 * @param string     $genericTypeLink   human-friendly explanation of the generic type of error
	 */
	public function add(
		string|int $genericCode,
		string $genericTitle,
		?string $specificDetails=null,
		?string $specificAboutLink=null,
		?string $genericTypeLink=null,
	): void {
		$errorObject = new ErrorObject($genericCode, $genericTitle, $specificDetails, $specificAboutLink, $genericTypeLink);
		
		$this->addErrorObject($errorObject);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @note also defines the http status code of the document if the ErrorObject has it defined
	 */
	public function addErrorObject(ErrorObject $errorObject): void {
		$this->errors[] = $errorObject;
		
		if ($errorObject->hasHttpStatusCode()) {
			$this->setHttpStatusCode($this->determineHttpStatusCode($errorObject->getHttpStatusCode()));
		}
	}
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray(): array {
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
	 */
	protected function determineHttpStatusCode(string|int $httpStatusCode): int {
		// add the new code
		$category = substr((string) $httpStatusCode, 0, 1);
		$category .= 'xx'; // help phpstan understand the array-key is a string not an int
		$this->httpStatusCodes[$category][(int) $httpStatusCode] = true;
		
		$advisedStatusCode = (int) $httpStatusCode;
		
		// when there's multiple, give preference to 5xx errors
		if (isset($this->httpStatusCodes['5xx']) && isset($this->httpStatusCodes['4xx'])) {
			// use a generic one
			$advisedStatusCode = 500;
		}
		elseif (isset($this->httpStatusCodes['5xx'])) {
			if (count($this->httpStatusCodes['5xx']) === 1) {
				$advisedStatusCode = key($this->httpStatusCodes['5xx']);
			}
			else {
				// use a generic one
				$advisedStatusCode = 500;
			}
		}
		elseif (isset($this->httpStatusCodes['4xx'])) {
			if (count($this->httpStatusCodes['4xx']) === 1) {
				$advisedStatusCode = key($this->httpStatusCodes['4xx']);
			}
			else {
				// use a generic one
				$advisedStatusCode = 400;
			}
		}
		
		return $advisedStatusCode;
	}
}
