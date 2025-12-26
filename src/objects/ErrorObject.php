<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\helpers\Converter;
use alsvanzelf\jsonapi\helpers\HttpStatusCodeManager;
use alsvanzelf\jsonapi\helpers\LinksManager;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\interfaces\HasLinksInterface;
use alsvanzelf\jsonapi\interfaces\HasMetaInterface;
use alsvanzelf\jsonapi\objects\AbstractObject;

class ErrorObject extends AbstractObject implements HasLinksInterface, HasMetaInterface {
	use HttpStatusCodeManager;
	use LinksManager;
	
	protected string|int $id;
	protected string $code;
	protected string $title;
	protected string $detail;
	/** @var array{pointer?: string, parameter?: string, header?: string} */
	protected array $source = [];
	protected MetaObject $meta;
	/** @var PHPStanTypeAlias_InternalOptions */
	protected static array $defaults = [
		/**
		 * add the trace of exceptions when adding exceptions
		 * in some cases it might be handy to disable if traces are too big
		 */
		'includeExceptionTrace' => true,
		
		/**
		 * strip a base path from exception file and trace paths
		 * set this to the applications root to have more readable exception responses
		 */
		'stripExceptionBasePath' => null,
	];
	
	/**
	 * @param string|int|null $genericCode       developer-friendly code of the generic type of error
	 * @param ?string         $genericTitle      human-friendly title of the generic type of error
	 * @param ?string         $specificDetails   human-friendly explanation of the specific error
	 * @param ?string         $specificAboutLink human-friendly explanation of the specific error
	 * @param ?string         $genericTypeLink   human-friendly explanation of the generic type of error
	 */
	public function __construct(
		string|int|null $genericCode=null,
		?string $genericTitle=null,
		?string $specificDetails=null,
		?string $specificAboutLink=null,
		?string $genericTypeLink=null,
	) {
		if ($genericCode !== null) {
			$this->setApplicationCode($genericCode);
		}
		if ($genericTitle !== null) {
			$this->setHumanExplanation($genericTitle, $specificDetails, $specificAboutLink, $genericTypeLink);
		}
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param PHPStanTypeAlias_InternalOptions $options {@see ErrorObject::$defaults}
	 */
	public static function fromException(\Throwable $exception, array $options=[]): self {
		$options = array_merge(self::$defaults, $options);
		
		$errorObject = new self();
		
		$className = $exception::class;
		if (strpos($className, '\\')) {
			$exploded  = explode('\\', $className);
			$className = end($exploded);
		}
		$errorObject->setApplicationCode(Converter::camelCaseToWords($className));
		
		$filePath = $exception->getFile();
		if ($options['stripExceptionBasePath'] !== null) {
			$filePath = str_replace($options['stripExceptionBasePath'], '', $filePath);
		}
		
		$metaObject = MetaObject::fromArray([
			'type'    => $exception::class,
			'message' => $exception->getMessage(),
			'code'    => $exception->getCode(),
			'file'    => $filePath,
			'line'    => $exception->getLine(),
		]);
		
		if ($options['includeExceptionTrace']) {
			$trace = $exception->getTrace();
			if ($options['stripExceptionBasePath'] !== null) {
				foreach ($trace as &$traceElement) {
					if (isset($traceElement['file'])) {
						$traceElement['file'] = str_replace($options['stripExceptionBasePath'], '', $traceElement['file']);
					}
				}
			}
			
			$metaObject->add('trace', $trace);
		}
		
		$errorObject->setMetaObject($metaObject);
		
		if (Validator::checkHttpStatusCode($exception->getCode())) {
			$errorObject->setHttpStatusCode($exception->getCode());
		}
		
		return $errorObject;
	}
	
	/**
	 * explain this particular occurence of the error in a human-friendly way
	 * 
	 * @param string  $genericTitle      title of the generic type of error
	 * @param ?string $specificDetails   explanation of the specific error
	 * @param ?string $specificAboutLink explanation of the specific error
	 * @param ?string $genericTypeLink   explanation of the generic type of error
	 */
	public function setHumanExplanation(
		string $genericTitle,
		?string $specificDetails=null,
		?string $specificAboutLink=null,
		?string $genericTypeLink=null,
	): void {
		$this->setHumanTitle($genericTitle);
		
		if ($specificDetails !== null) {
			$this->setHumanDetails($specificDetails);
		}
		if ($specificAboutLink !== null) {
			$this->setAboutLink($specificAboutLink);
		}
		if ($genericTypeLink !== null) {
			$this->setTypeLink($genericTypeLink);
		}
	}
	
	/**
	 * set the link about this specific occurence of the error, explained in a human-friendly way
	 * 
	 * @param array<string, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setAboutLink(string $href, array $meta=[]): void {
		$this->addLink('about', $href, $meta);
	}
	
	/**
	 * set the link of the generic type of this error, explained in a human-friendly way
	 * 
	 * @param array<string, mixed> $meta if given a LinkObject is added, otherwise a link string is added
	 */
	public function setTypeLink(string $href, array $meta=[]): void {
		$this->addLink('type', $href, $meta);
	}
	
	/**
	 * blame the json pointer from the request body causing this error
	 * e.g. "/data/attributes/title" or "/data"
	 * 
	 * @see https://tools.ietf.org/html/rfc6901
	 */
	public function blameJsonPointer(string $pointer): void {
		$this->addSource('pointer', $pointer);
	}
	
	/**
	 * blame the query parameter from the request causing this error
	 */
	public function blameQueryParameter(string $parameter): void {
		$this->addSource('parameter', $parameter);
	}
	
	/**
	 * blame the header from the request causing this error
	 */
	public function blameHeader(string $headerName): void {
		$this->addSource('header', $headerName);
	}
	
	public function addMeta(string $key, mixed $value): void {
		if (isset($this->meta) === false) {
			$this->setMetaObject(new MetaObject());
		}
		
		$this->meta->add($key, $value);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * a unique identifier for this specific occurrence of the error
	 */
	public function setUniqueIdentifier(string|int $id): void {
		$this->id = $id;
	}
	
	/**
	 * a code expressing the generic type of this error
	 * it should be application-specific and aimed at developers
	 * ints will be casted to a string
	 */
	public function setApplicationCode(string|int $genericCode): void {
		$this->code = (string) $genericCode;
	}
	
	/**
	 * add the source of the error
	 * 
	 * @see ->blameJsonPointer()
	 * @see ->blameQueryParameter()
	 */
	public function addSource(string $key, string $value): void {
		Validator::checkMemberName($key);
		
		$this->source[$key] = $value;
	}
	
	/**
	 * a short human-friendly explanation of the generic type of this error
	 */
	public function setHumanTitle(string $genericTitle): void {
		$this->title = $genericTitle;
	}
	
	/**
	 * a human-friendly explanation of this specific occurrence of the error
	 */
	public function setHumanDetails(string $specificDetails): void {
		$this->detail = $specificDetails;
	}
	
	public function setMetaObject(MetaObject $metaObject): void {
		$this->meta = $metaObject;
	}
	
	/**
	 * ObjectInterface
	 */
	
	public function isEmpty(): bool {
		if (isset($this->id)) {
			return false;
		}
		if ($this->hasHttpStatusCode()) {
			return false;
		}
		if (isset($this->code)) {
			return false;
		}
		if (isset($this->title)) {
			return false;
		}
		if (isset($this->detail)) {
			return false;
		}
		if ($this->hasLinks()) {
			return false;
		}
		if ($this->source !== []) {
			return false;
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			return false;
		}
		if ($this->hasAtMembers()) {
			return false;
		}
		if ($this->hasExtensionMembers()) {
			return false;
		}
		
		return true;
	}
	
	public function toArray(): array {
		$array = [];
		
		if ($this->hasAtMembers()) {
			$array = array_merge($array, $this->getAtMembers());
		}
		if ($this->hasExtensionMembers()) {
			$array = array_merge($array, $this->getExtensionMembers());
		}
		if (isset($this->id)) {
			$array['id'] = $this->id;
		}
		if ($this->hasHttpStatusCode()) {
			$array['status'] = (string) $this->getHttpStatusCode();
		}
		if (isset($this->code)) {
			$array['code'] = $this->code;
		}
		if (isset($this->title)) {
			$array['title'] = $this->title;
		}
		if (isset($this->detail)) {
			$array['detail'] = $this->detail;
		}
		if ($this->hasLinks()) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->source !== []) {
			$array['source'] = $this->source;
		}
		if (isset($this->meta) && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
