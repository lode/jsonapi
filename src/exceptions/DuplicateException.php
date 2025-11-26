<?php

namespace alsvanzelf\jsonapi\exceptions;

use alsvanzelf\jsonapi\exceptions\Exception;

class DuplicateException extends Exception {
	public function __construct($message='', $code=409, $previous=null) {
		parent::__construct($message, $code, $previous);
	}
}
