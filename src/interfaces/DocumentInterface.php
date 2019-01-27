<?php

namespace alsvanzelf\jsonapi\interfaces;

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
	 * @param  array $array optional, falls back to {@see ->toArray()}
	 * @return string json
	 */
	public function toJson(array $array=null);
	
	/**
	 * send jsonapi response to the browser
	 * 
	 * @note will set http status code and echo json
	 * 
	 * @param  string $json optional, falls back to {@see ->toJson()}
	 */
	public function sendResponse($json=null);
}
