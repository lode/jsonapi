<?php

namespace alsvanzelf\jsonapi\interfaces;

interface ObjectInterface {
	/**
	 * @return boolean
	 */
	public function isEmpty();
	
	/**
	 * @return array
	 */
	public function toArray();
}
