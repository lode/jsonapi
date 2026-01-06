<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\exceptions\Exception;

interface DocumentInterface {
	/**
	 * generate array with the contents of the document, used by {@see ->toJson()}
	 * 
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
	
	/**
	 * generate json with the contents of the document, used by {@see ->sendResponse()}
	 * 
	 * @param PHPStanTypeAlias_Options_Document $options
	 * 
	 * @throws Exception if generating json fails
	 */
	public function toJson(array $options=[]): string;
	
	/**
	 * send jsonapi response to the browser
	 * 
	 * @note will set http status code and content type, and echo json
	 * 
	 * @param PHPStanTypeAlias_Options_Document $options
	 */
	public function sendResponse(array $options=[]): void;
}
