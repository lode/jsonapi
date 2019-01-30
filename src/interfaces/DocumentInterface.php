<?php

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\Document;
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
	 * @param  array   $array       optional, falls back to {@see ->toArray()}
	 * @param  boolean $prettyPrint optional, defaults to false
	 * @return string json
	 * 
	 * @throws Exception if generating json fails
	 */
	public function toJson(array $array=null, $prettyPrint=false);
	
	/**
	 * send jsonapi response to the browser
	 * 
	 * @note will set http status code and content type, and echo json
	 * 
	 * @param  string  $json        optional, falls back to {@see ->toJson()}
	 * @param  string  $contentType optional, defaults to Document::CONTENT_TYPE_DEFAULT {@see Document::CONTENT_TYPE_*}
	 * @param  boolean $prettyPrint optional, defaults to false
	 */
	public function sendResponse($json=null, $contentType=Document::CONTENT_TYPE_DEFAULT, $prettyPrint=false);
	
	/**
	 * send jsonapi response to the browser with a jsonp callback wrapper
	 * 
	 * @note will set http status code and content type, and echo json
	 * 
	 * @param  string  $callback    defaults to Document::JSONP_CALLBACK_DEFAULT
	 * @param  string  $json        optional, falls back to {@see ->toJson()}
	 * @param  boolean $prettyPrint optional, defaults to false
	 */
	public function sendJsonpResponse($callback=Document::JSONP_CALLBACK_DEFAULT, $json=null, $prettyPrint=false);
}
