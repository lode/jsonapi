<?php

namespace alsvanzelf\jsonapi\extensions;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\extensions\AtomicOperationsExtension;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

/**
 * document to send results of an atomic operations API
 */
class AtomicOperationsDocument extends Document {
	/** @var AtomicOperationsExtension */
	private $extension;
	/** @var ResourceInterface[] */
	private $results = [];
	
	/**
	 * start the document, auto applies the extension
	 */
	public function __construct() {
		parent::__construct();
		
		$this->extension = new AtomicOperationsExtension();
		$this->applyExtension($this->extension);
	}
	
	/**
	 * add resources as results of the operations
	 * 
	 * @param ResourceInterface[] ...$resources
	 */
	public function addResults(ResourceInterface ...$resources) {
		$this->results = array_merge($this->results, $resources);
	}
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray() {
		$results = [];
		foreach ($this->results as $result) {
			$results[] = [
				'data' => $result->getResource()->toArray(),
			];
		}
		
		$this->addExtensionMember($this->extension, 'results', $results);
		
		return parent::toArray();
	}
}
