<?php

namespace alsvanzelf\jsonapi\interfaces;

interface ProfileInterface {
	/**
	 * the unique link identifying and describing the profile
	 * 
	 * @internal
	 * 
	 * @return string
	 */
	public function getOfficialLink();
}
