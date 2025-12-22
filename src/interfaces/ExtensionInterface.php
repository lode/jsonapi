<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

interface ExtensionInterface {
	/**
	 * the unique link identifying and describing the extension
	 * 
	 * @internal
	 */
	public function getOfficialLink(): string;
	
	/**
	 * get the extension's namespace
	 */
	public function getNamespace(): string;
}
