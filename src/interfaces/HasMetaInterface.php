<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface HasMetaInterface {
	public function addMeta(string $key, mixed $value): void;
}
