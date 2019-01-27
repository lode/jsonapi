<?php

namespace alsvanzelf\jsonapi\interfaces;

interface ObjectInterface {
	/**
	 * whether the object contains something for output
	 * 
	 * @return boolean
	 */
	public function isEmpty();
	
	/**
	 * generate array with the contents of the object
	 * 
	 * @return array
	 */
	public function toArray();
}
