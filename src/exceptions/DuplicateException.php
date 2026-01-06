<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\exceptions;

use alsvanzelf\jsonapi\exceptions\Exception;

class DuplicateException extends Exception {
	public function __construct(string $message='', int $code=409, ?\Throwable $previous=null) {
		parent::__construct($message, $code, $previous);
	}
}
