<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\Validator;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\objects\ResourceObject;

class DataDocument extends Document {
	/** @var ResourceObject[] */
	public $includedResources = [];
	/** @var Validator */
	protected $validator;
	
	public function __construct() {
		parent::__construct();
		
		$this->validator = new Validator();
	}
	
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	/**
	 * @param ResourceObject ...$resourceObjects
	 */
	public function addIncludedResourceObject(ResourceObject ...$resourceObjects) {
		foreach ($resourceObjects as $resourceObject) {
			try {
				$this->validator->checkUsedResourceIdentifier($resourceObject);
			}
			catch (DuplicateException $e) {
				// silently skip duplicates
				continue;
			}
			
			$this->includedResources[] = $resourceObject;
			
			$this->validator->markUsedResourceIdentifier($resourceObject);
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
		
		$array['data'] = null;
		
		if ($this->includedResources !== []) {
			$array['included'] = [];
			foreach ($this->includedResources as $resource) {
				$array['included'][] = $resource->toArray();
			}
		}
		
		return $array;
	}
}
