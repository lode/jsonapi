<?php

namespace alsvanzelf\jsonapi\exceptions;

use alsvanzelf\jsonapi\exceptions\Exception;

class InputException extends Exception {
	public function __construct($message='', $code=400, $previous=null) {
		parent::__construct($message, $code, $previous);
	}
}
