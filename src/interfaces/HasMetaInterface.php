<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface HasMetaInterface {
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function addMeta($key, $value);
}
