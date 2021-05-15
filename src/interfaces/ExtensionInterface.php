<?php

namespace alsvanzelf\jsonapi\interfaces;

interface ExtensionInterface {
	/**
	 * the unique link identifying and describing the extension
	 * 
	 * @internal
	 * 
	 * @return string
	 */
	public function getOfficialLink();
	
	/**
	 * get the extension's namespace
	 * 
	 * @return string
	 */
	public function getNamespace();
}
