<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;
use alsvanzelf\jsonapi\objects\JsonapiObject;
use alsvanzelf\jsonapi\objects\MetaObject;

abstract class Document implements DocumentInterface {
	const JSONAPI_VERSION_1_0 = '1.0';
	const JSONAPI_VERSION_1_1 = '1.0';
	const JSONAPI_VERSION_DEFAULT = Document::JSONAPI_VERSION_1_0;
	
	const META_LEVEL_ROOT     = 'root';
	const META_LEVEL_JSONAPI  = 'jsonapi';
	const META_LEVEL_RESOURCE = 'resource';
	
	/** @var int */
	public $httpStatusCode = 200;
	/** @var MetaObject */
	public $meta;
	/** @var JsonapiObject */
	public $jsonapi;
	
	public function __construct() {
		$this->setJsonapiObject(new JsonapiObject());
	}
	
	/**
	 * options
	 */
	
	/**
	 * @param int $statusCode
	 */
	public function setHttpStatusCode($statusCode) {
		$this->httpStatusCode = $statusCode;
	}
	
	/**
	 * human api
	 */
	
	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::META_LEVEL_* constants, optional, defaults to Document::META_LEVEL_ROOT
	 * 
	 * @throws InputException if the $level is unknown
	 * @throws InputException if the $level is Document::META_LEVEL_RESOURCE
	 */
	public function addMeta($key, $value, $level=Document::META_LEVEL_ROOT) {
		if ($level === Document::META_LEVEL_ROOT) {
			if ($this->meta === null) {
				$this->setMetaObject(new MetaObject());
			}
			
			$this->meta->add($key, $value);
		}
		elseif ($level === Document::META_LEVEL_JSONAPI) {
			if ($this->jsonapi === null) {
				$this->setJsonapiObject(new JsonapiObject());
			}
			
			$this->jsonapi->addMeta($key, $value);
		}
		elseif ($level === Document::META_LEVEL_RESOURCE) {
			throw new InputException('meta level "resource" can only be set on a ResourceDocument');
		}
		else {
			throw new InputException('unknown meta level "'.$level.'"');
		}
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * @param MetaObject $metaObject
	 */
	public function setMetaObject(MetaObject $metaObject) {
		$this->meta = $metaObject;
	}
	
	/**
	 * @param JsonapiObject $jsonapiObject
	 */
	public function setJsonapiObject(JsonapiObject $jsonapiObject) {
		$this->jsonapi = $jsonapiObject;
	}
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = [];
		
		if ($this->jsonapi !== null && $this->jsonapi->isEmpty() === false) {
			$array['jsonapi'] = $this->jsonapi->toArray();
		}
		if ($this->meta !== null && $this->meta->isEmpty() === false) {
			$array['meta'] = $this->meta->toArray();
		}
		
		return $array;
	}
	
	/**
	 * @inheritDoc
	 */
	public function toJson(array $array=null) {
		$array = $array ?: $this->toArray();
		
		return json_encode($array, JSON_PRETTY_PRINT);
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendResponse($json=null) {
		if ($this->httpStatusCode === 204) {
			http_response_code($this->httpStatusCode);
			return;
		}
		
		$json = $json ?: $this->toJson();
		
		http_response_code($this->httpStatusCode);
		echo $json;
	}
}
