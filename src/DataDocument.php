<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;

class DataDocument extends Document {
	/**
	 * human api
	 */
	
	/**
	 * spec api
	 */
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		$array['data'] = null;
		
		return $array;
	}
}
