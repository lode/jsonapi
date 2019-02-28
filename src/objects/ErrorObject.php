<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\AtMemberManager;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\HttpStatusCodeManager;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinksObject;

class ErrorObject implements ObjectInterface {
	use AtMemberManager, HttpStatusCodeManager;
	
	/** @var string */
	protected $id;
	/** @var string */
	protected $code;
	/** @var string */
	protected $title;
	/** @var string */
	protected $detail;
	/** @var LinksObject */
	protected $links;
	/** @var array */
	protected $source = [];
	/** @var MetaObject */
	protected $meta;
	/** @var array */
	protected static $defaults = [
		'exceptionExposeDetails' => false,
		'exceptionExposeTrace'   => true,
		'exceptionStripBasePath' => null,
	];
	
	/**
	 * @param string|int $genericCode       developer-friendly code of the generic type of error
	 * @param string     $genericTitle      human-friendly title of the generic type of error
	 * @param string     $specificDetails   optional, human-friendly explanation of the specific error
	 * @param string     $specificAboutLink optional, human-friendly explanation of the specific error
	 */
	public function __construct($genericCode=null, $genericTitle=null, $specificDetails=null, $specificAboutLink=null) {
		if ($genericCode !== null) {
			$this->setApplicationCode($genericCode);
		}
		if ($genericTitle !== null) {
			$this->setHumanExplanation($genericTitle, $specificDetails, $specificAboutLink);
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param  \Exception|\Throwable $exception
	 * @param  array                 $options   optional {@see ErrorObject::$defaults}
	 * @return ErrorObject
	 * 
	 * @throws InputException if $exception is not \Exception or \Throwable
	 */
	public static function fromException($exception, array $options=[]) {
		if ($exception instanceof \Exception === false && $exception instanceof \Throwable === false) {
			throw new InputException('input is not a real exception in php5 or php7');
		}
		
		$options = array_merge(self::$defaults, $options);
		
		$errorObject = new self();
		
		if ($options['exceptionExposeDetails']) {
			$genericTitle = Converter::camelCaseToWords(get_class($exception));
			$errorObject->setHumanExplanation($genericTitle);
			
			$filePath = $exception->getFile();
			if ($options['exceptionStripBasePath'] !== null) {
				$filePath = str_replace($options['exceptionStripBasePath'], '', $filePath);
			}
			
			$metaObject = MetaObject::fromArray([
				'message' => $exception->getMessage(),
				'file'    => $filePath,
				'line'    => $exception->getLine(),
			]);
			
			if ($options['exceptionExposeTrace']) {
				$trace = $exception->getTrace();
				if ($options['exceptionStripBasePath'] !== null) {
					foreach ($trace as &$traceElement) {
						if (isset($traceElement['file'])) {
							$traceElement['file'] = str_replace($options['exceptionStripBasePath'], '', $traceElement['file']);
						}
					}
				}
				
				$metaObject->add('trace', $trace);
			}
			
			$errorObject->setMetaObject($metaObject);
		}
		
		if ($exception->getCode() !== 0) {
			$errorObject->setApplicationCode($exception->getCode());
			
			if (Validator::checkHttpStatusCode($exception->getCode())) {
				$errorObject->setHttpStatusCode($exception->getCode());
			}
		}
		
		return $errorObject;
	}
	
	/**
	 * explain this particular occurence of the error in a human-friendly way
	 * 
	 * @param string $genericTitle      title of the generic type of error
	 * @param string $specificDetails   optional, explanation of the specific error
	 * @param string $specificAboutLink optional, explanation of the specific error
	 */
	public function setHumanExplanation($genericTitle, $specificDetails=null, $specificAboutLink=null) {
		$this->setHumanTitle($genericTitle);
		
		if ($specificDetails !== null) {
			$this->setHumanDetails($specificDetails);
		}
		if ($specificAboutLink !== null) {
			$this->setAboutLink($specificAboutLink);
		}
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
	 * set the link about this specific occurence of the error, explained in a human-friendly way
	 * 
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setAboutLink($href, array $meta=[]) {
		$this->addLink('about', $href, $meta);
	}
	
	/**
	 * blame the json pointer from the request body causing this error
	 * 
	 * @see https://tools.ietf.org/html/rfc6901
	 * 
	 * @param  string $pointer e.g. "/data/attributes/title" or "/data"
	 */
	public function blameJsonPointer($pointer) {
		$this->addSource('pointer', $pointer);
	}
	
	/**
	 * blame the query parameter from the request causing this error
	 * 
	 * @param  string $parameter
	 */
	public function blameQueryParameter($parameter) {
		$this->addSource('parameter', $parameter);
	}
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function addMeta($key, $value) {
		if ($this->meta === null) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * a unique identifier for this specific occurrence of the error
	 * 
	 * @param string|int $id
	 */
	public function setUniqueIdentifier($id) {
		$this->id = $id;
	}
	
	/**
	 * a code expressing the generic type of this error
	 * it should be application-specific and aimed at developers
	 * 
	 * @param string|int $genericCode will be casted to a string
	 */
	public function setApplicationCode($genericCode) {
		$this->code = (string) $genericCode;
	}
	
	/**
	 * add the source of the error
	 * 
	 * @param string $key   {@see ->blameJsonPointer(), ->blameQueryParameter()}
	 * @param string $value
	 */
	public function addSource($key, $value) {
		Validator::checkMemberName($key);
		
		$this->source[$key] = $value;
	}
	
	/**
	 * a short human-friendly explanation of the generic type of this error
	 * 
	 * @param string $genericTitle
	 */
	public function setHumanTitle($genericTitle) {
		$this->title = $genericTitle;
	}
	
	/**
	 * a human-friendly explanation of this specific occurrence of the error
	 * 
	 * @param string $specificDetails
	 */
	public function setHumanDetails($specificDetails) {
		$this->detail = $specificDetails;
	}
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function isEmpty() {
		if ($this->id !== null) {
			return false;
		}
		if ($this->hasHttpStatusCode()) {
			return false;
		}
		if ($this->code !== null) {
			return false;
		}
		if ($this->title !== null) {
			return false;
		}
		if ($this->detail !== null) {
			return false;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			return false;
		}
		if ($this->source !== []) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = $this->getAtMembers();
		
		if ($this->id !== null) {
			$array['id'] = $this->id;
		}
		if ($this->hasHttpStatusCode()) {
			$array['status'] = (string) $this->getHttpStatusCode();
		}
		if ($this->code !== null) {
			$array['code'] = $this->code;
		}
		if ($this->title !== null) {
			$array['title'] = $this->title;
		}
		if ($this->detail !== null) {
			$array['detail'] = $this->detail;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->source !== []) {
			$array['source'] = $this->source;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
