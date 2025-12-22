<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface ProfileInterface {
	/**
	 * the unique link identifying and describing the profile
	 * 
	 * @internal
	 */
	public function getOfficialLink(): string;
}
