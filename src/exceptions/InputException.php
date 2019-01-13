<?php

namespace alsvanzelf\jsonapi\exceptions;

use alsvanzelf\jsonapi\exceptions\Exception;

class InputException extends Exception {
	public function __construct($message='', $code=400, \Exception $previous=null) {
		return parent::__construct($message, $code, $previous);
	}
}
