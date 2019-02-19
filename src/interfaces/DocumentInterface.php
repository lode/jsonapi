<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\exceptions\Exception;

interface DocumentInterface {
	/**
	 * generate array with the contents of the document, used by {@see ->toJson()}
	 * 
	 * @return array
	 */
	public function toArray();
	
	/**
	 * generate json with the contents of the document, used by {@see ->sendResponse()}
	 * 
	 * @param  array $options optional
	 * @return string json
	 * 
	 * @throws Exception if generating json fails
	 */
	public function toJson(array $options=[]);
	
	/**
	 * send jsonapi response to the browser
	 * 
	 * @note will set http status code and content type, and echo json
	 * 
	 * @param array $options optional
	 */
	public function sendResponse(array $options=[]);
}
