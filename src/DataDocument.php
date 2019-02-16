<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\exceptions\DuplicateException;
use alsvanzelf\jsonapi\helpers\Validator;
use alsvanzelf\jsonapi\objects\ResourceObject;

/**
 * @see ResourceDocument or CollectionDocument
 */
abstract class DataDocument extends Document {
	/** @var ResourceObject[] */
	protected $includedResources = [];
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
	 * internal api
	 */
	
	/**
	 * @internal
	 * 
	 * @param ResourceObject ...$resourceObjects
	 */
	public function addIncludedResourceObject(ResourceObject ...$resourceObjects) {
		foreach ($resourceObjects as $resourceObject) {
			try {
				$this->validator->claimUsedResourceIdentifier($resourceObject);
			}
			catch (DuplicateException $e) {
				// silently skip duplicates
				continue;
			}
			
			$this->includedResources[] = $resourceObject;
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
