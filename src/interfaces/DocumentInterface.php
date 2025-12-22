<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\exceptions\Exception;

interface DocumentInterface {
	/**
	 * generate array with the contents of the document, used by {@see ->toJson()}
	 */
	public function toArray(): array;
	
	/**
	 * generate json with the contents of the document, used by {@see ->sendResponse()}
	 * 
	 * @param TypeAlias_InternalOptions $options
	 * 
	 * @throws Exception if generating json fails
	 */
	public function toJson(array $options=[]): string;
	
	/**
	 * send jsonapi response to the browser
	 * 
	 * @note will set http status code and content type, and echo json
	 * 
	 * @param TypeAlias_InternalOptions $options
	 */
	public function sendResponse(array $options=[]): void;
}
