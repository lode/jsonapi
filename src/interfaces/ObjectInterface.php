<?php

namespace alsvanzelf\jsonapi\interfaces;

interface ObjectInterface {
	/**
	 * whether the object contains something for output
	 * 
	 * @internal
	 * 
	 * @return boolean
	 */
	public function isEmpty();
	
	/**
	 * generate array with the contents of the object
	 * 
	 * @internal
	 * 
	 * @return array
	 */
	public function toArray();
}
