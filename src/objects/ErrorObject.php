<?php

namespace alsvanzelf\jsonapi\objects;

use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\ObjectInterface;
use alsvanzelf\jsonapi\objects\LinksObject;

class ErrorObject implements ObjectInterface {
	/** @var string */
	public $id;
	/** @var LinksObject */
	public $links;
	/** @var string */
	public $status;
	/** @var string */
	public $code;
	/** @var string */
	public $title;
	/** @var string */
	public $detail;
	/** @var array */
	public $source = [];
	/** @var MetaObject */
	public $meta;
	
	/**
	 * human api
	 */
	
	/**
	 * @param  \Exception $exception
	 * @return ErrorObject
	 */
	public static function fromException(\Exception $exception) {
		$errorObject = new self();
		
		$errorObject->setGeneric($exception->getMessage(), $exception->getCode());
		$errorObject->addMeta('file', $exception->getFile());
		$errorObject->addMeta('line', $exception->getLine());
		$errorObject->addMeta('trace', $exception->getTrace());
		
		if (Validator::checkHttpStatusCode($exception->getCode())) {
			$errorObject->setHttpStatusCode($exception->getCode());
		}
		
		return $errorObject;
	}
	
	/**
	 * describe the generic type of this error, without anything about this particular occurence
	 * 
	 * @param string     $titleExplanation
	 * @param string|int $applicationCode  optional
	 */
	public function setGeneric($titleExplanation, $applicationCode=null) {
		$this->setTitle($titleExplanation);
		
		if ($applicationCode !== null) {
			$this->setApplicationCode($applicationCode);
			
			if (Validator::checkHttpStatusCode($applicationCode)) {
				$this->setHttpStatusCode($applicationCode);
			}
		}
	}
	
	/**
	 * describe this particular occurence of the error
	 * 
	 * @param string     $detailExplanation
	 * @param string|int $id                optional
	 * @param string     $aboutLink         optional
	 */
	public function setOccurence($detailExplanation, $id=null, $aboutLink=null) {
		$this->setDetailOccurence($detailExplanation);
		
		if ($id !== null) {
			$this->setUniqueIdentifier($id);
		}
		if ($aboutLink !== null) {
			$this->setAboutLink($aboutLink);
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
	 * set the link about this particular occurence of the error
	 * 
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setAboutLink($href, array $meta=[]) {
		$this->addLink('about', $href, $meta);
	}
	
	/**
	 * set the link where the end user can act to solve this particular occurence of the error
	 * 
	 * @note this is not a part of the official specification
	 * 
	 * @param string $href
	 * @param array  $meta optional, if given a LinkObject is added, otherwise a link string is added
	 */
	public function setActionLink($href, array $meta=[]) {
		$this->addLink('action', $href, $meta);
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
	 * blame the key in POST data from the request causing this error
	 * 
	 * @note this is not a part of the official specification
	 * 
	 * @param  string $postKey
	 */
	public function blamePostData($postKey) {
		$this->addSource('post', $postKey);
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
	 * a unique identifier for this particular occurrence of the error
	 * 
	 * @param string|int $id
	 */
	public function setUniqueIdentifier($id) {
		$this->id = $id;
	}
	
	/**
	 * @param LinksObject $linksObject
	 */
	public function setLinksObject(LinksObject $linksObject) {
		$this->links = $linksObject;
	}
	
	/**
	 * the HTTP status code applicable to this problem
	 * 
	 * @param string|int $httpStatusCode will be casted to a string
	 * 
	 * @throws InputException if an invalid code is used
	 */
	public function setHttpStatusCode($httpStatusCode) {
		if (Validator::checkHttpStatusCode($httpStatusCode) === false) {
			throw new InputException('can not use an invalid http status code');
		}
		
		$this->status = (string) $httpStatusCode;
	}
	
	/**
	 * an application-specific error code, expressed as a string value
	 * 
	 * @param string|int $applicationCode will be casted to a string
	 */
	public function setApplicationCode($applicationCode) {
		$this->code = (string) $applicationCode;
	}
	
	/**
	 * a short human friendly explanation of the generic type of this error
	 * 
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * a human friendly explanation of this particular occurrence of the error
	 * 
	 * @param string $detailExplanation
	 */
	public function setDetailOccurence($detailExplanation) {
		$this->detail = $detailExplanation;
	}
	
	/**
	 * add the source of the error
	 * 
	 * @param string $key   {@see ->blameJsonPointer(), ->blameQueryParameter(), ->blamePostData()}
	 * @param string $value
	 */
	public function addSource($key, $value) {
		$this->source[$key] = $value;
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
		if ($this->links !== null && $this->links->isEmpty() === false) {
			return false;
		}
		if ($this->status !== null) {
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
		if ($this->source !== []) {
			return false;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->id !== null) {
			$array['id'] = $this->id;
		}
		if ($this->links !== null && $this->links->isEmpty() === false) {
			$array['links'] = $this->links->toArray();
		}
		if ($this->status !== null) {
			$array['status'] = $this->status;
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
		if ($this->source !== []) {
			$array['source'] = $this->source;
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
}
