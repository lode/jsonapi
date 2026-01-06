<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\extensions;

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\extensions\AtomicOperationsExtension;
use alsvanzelf\jsonapi\interfaces\DocumentInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

/**
 * document to send results of an atomic operations API
 */
class AtomicOperationsDocument extends Document {
	private readonly AtomicOperationsExtension $extension;
	/** @var ResourceInterface[] */
	private array $results = [];
	
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
	public function addResults(ResourceInterface ...$resources): void {
		$this->results = [...$this->results, ...$resources];
	}
	
	/**
	 * DocumentInterface
	 */
	
	public function toArray(): array {
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
