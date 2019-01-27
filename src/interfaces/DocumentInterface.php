<?php

namespace alsvanzelf\jsonapi\interfaces;

interface DocumentInterface {
	/**
	 * @return array
	 */
	public function toArray();
	
	/**
	 * @param  array $array optional, falls back to ->toArray()
	 * @return string json
	 */
	public function toJson(array $array=null);
	
	/**
	 * @note will set http status code and echo json
	 * 
	 * @param  string $json optional, falls back to ->toJson()
	 */
	public function sendResponse($json=null);
}
