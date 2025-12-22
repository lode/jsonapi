<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\helpers;

use alsvanzelf\jsonapi\exceptions\InputException;
use alsvanzelf\jsonapi\helpers\Validator;

trait HttpStatusCodeManager {
	protected int $httpStatusCode;
	
	/**
	 * spec api
	 */
	
	/**
	 * @throws InputException if an invalid code is used
	 */
	public function setHttpStatusCode(int $httpStatusCode): void {
		if (Validator::checkHttpStatusCode($httpStatusCode) === false) {
			throw new InputException('can not use an invalid http status code');
		}
		
		$this->httpStatusCode = $httpStatusCode;
	}
	
	/**
	 * internal api
	 */
	
	/**
	 * @internal
	 */
	public function hasHttpStatusCode(): bool {
		return isset($this->httpStatusCode);
	}
	
	/**
	 * @internal
	 */
	public function getHttpStatusCode(): int {
		return $this->httpStatusCode;
	}
}
